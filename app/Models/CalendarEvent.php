<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Support\Uuid;
use PDO;
use PDOException;

class CalendarEvent
{
    private PDO $db;
    private ?array $tableColumns = null;
    private ?array $employeeColumns = null;
    private ?array $calendarColumns = null;

    private const EVENT_TYPES = ['holiday', 'shift', 'leave', 'meeting', 'reminder', 'task'];
    private const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function list(array $filters, string $start, string $end): array
    {
        $this->assertSchemaReady();
        $events = array_merge(
            $this->fetchCalendarEvents($start, $end),
            $this->fetchLeaveEvents($filters, $start, $end)
        );

        $events = array_values(array_filter($events, function (array $event) use ($filters): bool {
            return $this->matchesFilters($event, $filters);
        }));

        usort($events, static function (array $left, array $right): int {
            $dateCompare = strcmp((string) ($left['start_date'] ?? ''), (string) ($right['start_date'] ?? ''));
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            $allDayCompare = ((int) ($left['sort_all_day'] ?? 0) <=> (int) ($right['sort_all_day'] ?? 0));
            if ($allDayCompare !== 0) {
                return $allDayCompare;
            }

            $timeCompare = strcmp((string) ($left['sort_start_time'] ?? ''), (string) ($right['sort_start_time'] ?? ''));
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return strcmp((string) ($left['start'] ?? ''), (string) ($right['start'] ?? ''));
        });

        return [
            'events' => $events,
            'summary' => $this->buildSummary($events),
        ];
    }

    public function filters(): array
    {
        $this->assertSchemaReady();
        return [
            'employees' => $this->getEmployees(),
            'departments' => $this->getDistinctColumnValues('department'),
            'branches' => $this->getDistinctColumnValues('branch'),
            'event_types' => self::EVENT_TYPES,
            'statuses' => self::STATUSES,
        ];
    }

    public function getByUuid(string $uuid): ?array
    {
        $this->assertSchemaReady();
        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_calendar_events WHERE uuid = :uuid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':uuid' => $uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrateEvent($row, []);
    }

    public function create(array $data): array
    {
        $this->assertSchemaReady();
        $uuid = Uuid::v4();

        $payload = $this->normalizePayload($data);
        $payload['uuid'] = $uuid;
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['status'] = $payload['status'] ?? 'pending';

        $this->db->beginTransaction();

        try {
            $columns = [
                'uuid', 'title', 'description', 'event_type', 'status',
                'start_at', 'end_at', 'all_day', 'recurrence_rule',
                'recurrence_parent_id', 'created_at', 'created_by',
                'updated_at', 'updated_by', 'approved_at', 'approved_by',
                'rejected_at', 'rejected_by', 'cancelled_at', 'cancelled_by',
            ];

            $insert = [];
            $params = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $payload)) {
                    continue;
                }
                $insert[] = $column;
                $params[':' . $column] = $payload[$column];
            }

            $sql = 'INSERT INTO tbl_calendar_events (' . implode(', ', $insert) . ')
                    VALUES (' . implode(', ', array_map(static fn (string $column) => ':' . $column, $insert)) . ')';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $eventId = (int) $this->db->lastInsertId();
            $this->syncTargets($eventId, $payload['targets'] ?? []);

            $this->db->commit();

            return [
                'success' => true,
                'uuid' => $uuid,
                'id' => $eventId,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(string $uuid, array $data): bool
    {
        $this->assertSchemaReady();
        $event = $this->getRawByUuid($uuid);
        if (!$event) {
            return false;
        }

        $payload = $this->normalizePayload($data, false);
        $payload['updated_at'] = date('Y-m-d H:i:s');

        $set = [];
        $params = [':uuid' => $uuid];

        foreach ([
            'title', 'description', 'event_type', 'status', 'start_at',
            'end_at', 'all_day', 'recurrence_rule', 'updated_at',
            'updated_by', 'approved_at', 'approved_by', 'rejected_at',
            'rejected_by', 'cancelled_at', 'cancelled_by',
        ] as $column) {
            if (!array_key_exists($column, $payload)) {
                continue;
            }

            $set[] = $column . ' = :' . $column;
            $params[':' . $column] = $payload[$column];
        }

        if (!empty($set)) {
            $stmt = $this->db->prepare(
                'UPDATE tbl_calendar_events SET ' . implode(', ', $set) .
                ' WHERE uuid = :uuid AND deleted_at IS NULL'
            );
            $stmt->execute($params);
        }

        if (array_key_exists('targets', $payload)) {
            $this->syncTargets((int) $event['id'], $payload['targets']);
        }

        return true;
    }

    public function delete(string $uuid, ?int $deletedBy = null): bool
    {
        $this->assertSchemaReady();
        $stmt = $this->db->prepare(
            'UPDATE tbl_calendar_events
             SET deleted_at = NOW(), deleted_by = :deleted_by
             WHERE uuid = :uuid AND deleted_at IS NULL'
        );
        return $stmt->execute([
            ':deleted_by' => $deletedBy,
            ':uuid' => $uuid,
        ]);
    }

    public function updateStatus(string $uuid, string $status, ?int $actorId = null): bool
    {
        $this->assertSchemaReady();
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $fields = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorId,
        ];

        if ($status === 'approved') {
            $fields['approved_at'] = date('Y-m-d H:i:s');
            $fields['approved_by'] = $actorId;
        } elseif ($status === 'rejected') {
            $fields['rejected_at'] = date('Y-m-d H:i:s');
            $fields['rejected_by'] = $actorId;
        } elseif ($status === 'cancelled') {
            $fields['cancelled_at'] = date('Y-m-d H:i:s');
            $fields['cancelled_by'] = $actorId;
        }

        $set = [];
        $params = [':uuid' => $uuid];
        foreach ($fields as $key => $value) {
            $set[] = $key . ' = :' . $key;
            $params[':' . $key] = $value;
        }

        $stmt = $this->db->prepare(
            'UPDATE tbl_calendar_events SET ' . implode(', ', $set) .
            ' WHERE uuid = :uuid AND deleted_at IS NULL'
        );

        return $stmt->execute($params);
    }

    public function getTargetsByUuid(string $uuid): array
    {
        $this->assertSchemaReady();
        $stmt = $this->db->prepare(
            'SELECT t.target_type, t.target_value, t.target_label
             FROM tbl_calendar_event_targets t
             INNER JOIN tbl_calendar_events e ON e.id = t.event_id
             WHERE e.uuid = :uuid
             ORDER BY t.id ASC'
        );
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchCalendarEvents(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_calendar_events
             WHERE deleted_at IS NULL
               AND start_at <= :end_at
               AND end_at >= :start_at
             ORDER BY start_at ASC'
        );
        $stmt->execute([
            ':start_at' => $start . ' 00:00:00',
            ':end_at' => $end . ' 23:59:59',
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eventIds = array_column($rows, 'id');
        $targetsByEventId = $this->loadTargets($eventIds);
        $results = [];

        foreach ($rows as $row) {
            $results = array_merge(
                $results,
                $this->expandRecurringEvent($row, $targetsByEventId[(int) $row['id']] ?? [], $start, $end)
            );
        }

        return $results;
    }

    private function fetchLeaveEvents(array $filters, string $start, string $end): array
    {
        $where = [
            'la.deleted_at IS NULL',
            'la.start_date <= :end_date',
            'la.end_date >= :start_date',
        ];
        $params = [
            ':start_date' => $start,
            ':end_date' => $end,
        ];

        if (!empty($filters['employee_id'])) {
            $where[] = 'la.employee_id = :employee_id';
            $params[':employee_id'] = (int) $filters['employee_id'];
        }

        if (!empty($filters['department'])) {
            $where[] = 'e.department = :department';
            $params[':department'] = (string) $filters['department'];
        }

        if (!empty($filters['branch']) && $this->employeeHasColumn('branch')) {
            $where[] = 'e.branch = :branch';
            $params[':branch'] = (string) $filters['branch'];
        }

        $sql = '
            SELECT
                la.uuid,
                la.reason AS title,
                la.reason AS description,
                \'leave\' AS event_type,
                CASE
                    WHEN la.status_id = 0 THEN \'pending\'
                    WHEN la.status_id = 1 THEN \'approved\'
                    WHEN la.status_id = 2 THEN \'rejected\'
                    ELSE \'cancelled\'
                END AS status,
                CONCAT(la.start_date, \' 00:00:00\') AS start_at,
                CONCAT(la.end_date, \' 23:59:59\') AS end_at,
                1 AS all_day,
                NULL AS recurrence_rule,
                e.id AS employee_id,
                e.full_name AS employee_name,
                e.department,
                ' . ($this->employeeHasColumn('branch') ? 'e.branch' : 'NULL') . ' AS branch,
                lt.name AS leave_type,
                la.status_id,
                la.remark,
                la.created_at,
                la.approved_at
            FROM tbl_leave_applications la
            INNER JOIN tbl_employees e ON e.id = la.employee_id
            INNER JOIN tbl_leave_types lt ON lt.id = la.leave_type_id
            WHERE ' . implode(' AND ', $where) . '
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $row) {
            $events[] = [
                'id' => 'leave-' . $row['uuid'],
                'uuid' => $row['uuid'],
                'source_type' => 'leave',
                'title' => $row['employee_name'] . ' - ' . ($row['leave_type'] ?? 'Leave'),
                'description' => $row['remark'] ?: $row['description'],
                'event_type' => 'leave',
                'status' => $row['status'],
                'start' => $row['start_at'],
                'end' => $row['end_at'],
                'all_day' => true,
                'employee_id' => (int) ($row['employee_id'] ?? 0),
                'employee_name' => $row['employee_name'],
                'department' => $row['department'] ?? null,
                'branch' => $row['branch'] ?? null,
                'leave_type' => $row['leave_type'] ?? null,
                'leave_status_id' => (int) ($row['status_id'] ?? 0),
                'remark' => $row['remark'] ?? null,
                'targets' => [
                    ['type' => 'employee', 'value' => (string) $row['employee_id'], 'label' => $row['employee_name']],
                    ['type' => 'department', 'value' => (string) ($row['department'] ?? ''), 'label' => $row['department'] ?? ''],
                ],
                'meta' => [
                    'leave_uuid' => $row['uuid'],
                    'leave_type' => $row['leave_type'] ?? null,
                    'status_id' => (int) ($row['status_id'] ?? 0),
                ],
            ];
        }

        return $events;
    }

    private function expandRecurringEvent(array $row, array $targets, string $rangeStart, string $rangeEnd): array
    {
        $start = new \DateTimeImmutable((string) $row['start_at']);
        $end = new \DateTimeImmutable((string) $row['end_at']);
        $rangeStartDt = new \DateTimeImmutable($rangeStart . ' 00:00:00');
        $rangeEndDt = new \DateTimeImmutable($rangeEnd . ' 23:59:59');

        $rule = [];
        if (!empty($row['recurrence_rule'])) {
            $decoded = json_decode((string) $row['recurrence_rule'], true);
            $rule = is_array($decoded) ? $decoded : [];
        }

        if (empty($rule) || (($rule['frequency'] ?? 'none') === 'none')) {
            if ($end < $rangeStartDt || $start > $rangeEndDt) {
                return [];
            }
            return [$this->hydrateEvent($row, $targets)];
        }

        $frequency = strtolower((string) ($rule['frequency'] ?? 'none'));
        $interval = max(1, (int) ($rule['interval'] ?? 1));
        $until = !empty($rule['until']) ? new \DateTimeImmutable((string) $rule['until'] . ' 23:59:59') : null;
        $days = array_map('strtolower', array_map('trim', (array) ($rule['days'] ?? [])));
        $durationSeconds = max(60, $end->getTimestamp() - $start->getTimestamp());

        $occurrences = [];
        $cursor = $rangeStartDt > $start ? $rangeStartDt : $start;
        $loopGuard = 0;

        while ($cursor <= $rangeEndDt && $loopGuard < 400) {
            $loopGuard++;
            if ($until !== null && $cursor > $until) {
                break;
            }

            if ($this->occursOnDate($cursor, $start, $frequency, $interval, $days)) {
                $occurrenceStart = $cursor->setTime(
                    (int) $start->format('H'),
                    (int) $start->format('i'),
                    (int) $start->format('s')
                );
                $occurrenceEnd = $occurrenceStart->modify('+' . $durationSeconds . ' seconds');
                $occurrenceRow = $row;
                $occurrenceRow['start_at'] = $occurrenceStart->format('Y-m-d H:i:s');
                $occurrenceRow['end_at'] = $occurrenceEnd->format('Y-m-d H:i:s');
                $occurrenceRow['recurrence_parent_id'] = (int) $row['id'];
                $occurrenceRow['recurrence_rule'] = $row['recurrence_rule'];
                $occurrences[] = $this->hydrateEvent($occurrenceRow, $targets, true);
            }

            $cursor = $cursor->modify('+1 day');
        }

        return $occurrences;
    }

    private function occursOnDate(\DateTimeImmutable $cursor, \DateTimeImmutable $start, string $frequency, int $interval, array $days): bool
    {
        if ($cursor < $start) {
            return false;
        }

        $diffDays = (int) $start->diff($cursor)->format('%a');

        return match ($frequency) {
            'daily' => $diffDays % $interval === 0,
            'weekly' => $this->weeklyMatch($cursor, $start, $interval, $days),
            'monthly' => $this->monthlyMatch($cursor, $start, $interval),
            'yearly' => $this->yearlyMatch($cursor, $start, $interval),
            default => false,
        };
    }

    private function weeklyMatch(\DateTimeImmutable $cursor, \DateTimeImmutable $start, int $interval, array $days): bool
    {
        $weekday = strtolower($cursor->format('D'));
        $weekdayMap = [
            'sun' => 'sun', 'mon' => 'mon', 'tue' => 'tue',
            'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri', 'sat' => 'sat',
        ];

        if (!empty($days)) {
            $days = array_map(static fn ($day) => strtolower(substr((string) $day, 0, 3)), $days);
            if (!in_array($weekdayMap[$weekday] ?? $weekday, $days, true)) {
                return false;
            }
            $weekDiff = intdiv((int) $start->diff($cursor)->format('%a'), 7);
            return $weekDiff % $interval === 0;
        }

        $weekDiff = intdiv((int) $start->diff($cursor)->format('%a'), 7);
        return $weekday === strtolower($start->format('D')) && $weekDiff % $interval === 0;
    }

    private function monthlyMatch(\DateTimeImmutable $cursor, \DateTimeImmutable $start, int $interval): bool
    {
        if ((int) $cursor->format('j') !== (int) $start->format('j')) {
            return false;
        }

        $startMonth = (int) $start->format('n');
        $cursorMonth = (int) $cursor->format('n');
        $startYear = (int) $start->format('Y');
        $cursorYear = (int) $cursor->format('Y');
        $monthDiff = (($cursorYear - $startYear) * 12) + ($cursorMonth - $startMonth);

        return $monthDiff >= 0 && $monthDiff % $interval === 0;
    }

    private function yearlyMatch(\DateTimeImmutable $cursor, \DateTimeImmutable $start, int $interval): bool
    {
        return $cursor->format('m-d') === $start->format('m-d')
            && (((int) $cursor->format('Y')) - ((int) $start->format('Y'))) % $interval === 0;
    }

    private function hydrateEvent(array $row, array $targets, bool $isOccurrence = false): array
    {
        $scope = $this->summarizeTargets($targets);
        $dateRange = $this->formatEventDateRange(
            (string) ($row['start_at'] ?? ''),
            (string) ($row['end_at'] ?? ''),
            (bool) ($row['all_day'] ?? false)
        );
        $timeRange = $this->formatEventTimeRange(
            (string) ($row['start_at'] ?? ''),
            (string) ($row['end_at'] ?? ''),
            (bool) ($row['all_day'] ?? false)
        );

        return [
            'id' => $row['id'],
            'uuid' => $row['uuid'],
            'source_type' => 'calendar',
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'event_type' => $row['event_type'],
            'status' => $row['status'],
            'start' => $row['start_at'],
            'end' => $row['end_at'],
            'start_date' => substr((string) ($row['start_at'] ?? ''), 0, 10),
            'end_date' => substr((string) ($row['end_at'] ?? ''), 0, 10),
            'start_time' => $this->extractTime((string) ($row['start_at'] ?? '')),
            'end_time' => $this->extractTime((string) ($row['end_at'] ?? '')),
            'all_day' => (bool) ($row['all_day'] ?? false),
            'is_all_day' => (bool) ($row['all_day'] ?? false),
            'date_label' => $dateRange,
            'time_label' => $timeRange,
            'sort_all_day' => (bool) ($row['all_day'] ?? false) ? 0 : 1,
            'sort_start_time' => $this->extractTime((string) ($row['start_at'] ?? '')),
            'is_recurring' => !empty($row['recurrence_rule']),
            'is_occurrence' => $isOccurrence,
            'recurrence_rule' => $row['recurrence_rule'] ? json_decode((string) $row['recurrence_rule'], true) : null,
            'scope' => $scope,
            'targets' => $targets,
            'employee_id' => $scope['employee_id'] ?? null,
            'employee_name' => $scope['employee_name'] ?? null,
            'department' => $scope['department'] ?? null,
            'branch' => $scope['branch'] ?? null,
            'team' => $scope['team'] ?? null,
            'meta' => [
                'created_by' => (int) ($row['created_by'] ?? 0),
                'updated_by' => (int) ($row['updated_by'] ?? 0),
                'approved_by' => $row['approved_by'] ?? null,
                'rejected_by' => $row['rejected_by'] ?? null,
                'cancelled_by' => $row['cancelled_by'] ?? null,
            ],
        ];
    }

    public function formatEventDate(array $event): string
    {
        return $this->formatEventDateRange(
            (string) ($event['start_at'] ?? $event['start'] ?? ''),
            (string) ($event['end_at'] ?? $event['end'] ?? ''),
            (bool) ($event['all_day'] ?? $event['is_all_day'] ?? false)
        );
    }

    public function formatEventTime(array $event): string
    {
        return $this->formatEventTimeRange(
            (string) ($event['start_at'] ?? $event['start'] ?? ''),
            (string) ($event['end_at'] ?? $event['end'] ?? ''),
            (bool) ($event['all_day'] ?? $event['is_all_day'] ?? false)
        );
    }

    private function formatEventDateRange(string $startAt, string $endAt, bool $allDay): string
    {
        $startDate = $this->parseDatePart($startAt);
        $endDate = $this->parseDatePart($endAt);

        if ($startDate === null) {
            return '--';
        }

        if ($allDay && $endDate !== null && $startDate !== $endDate) {
            return $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
        }

        return $startDate->format('M d, Y');
    }

    private function formatEventTimeRange(string $startAt, string $endAt, bool $allDay): string
    {
        if ($allDay) {
            return 'All Day';
        }

        $startTime = $this->extractTime($startAt);
        $endTime = $this->extractTime($endAt);
        if ($startTime === '--' || $endTime === '--') {
            return '--';
        }

        return $this->formatTimeForDisplay($startAt) . ' - ' . $this->formatTimeForDisplay($endAt);
    }

    private function parseDatePart(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable(substr($value, 0, 10));
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractTime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '--';
        }

        $ts = strtotime($value);
        return $ts === false ? '--' : date('H:i:s', $ts);
    }

    private function formatTimeForDisplay(string $value): string
    {
        $ts = strtotime($value);
        return $ts === false ? '--' : date('h:i A', $ts);
    }

    private function summarizeTargets(array $targets): array
    {
        if (empty($targets)) {
            return ['type' => 'company', 'label' => 'Company-wide'];
        }

        $summary = [
            'type' => 'mixed',
            'label' => '',
            'employee_name' => null,
            'employee_id' => null,
            'department' => null,
            'branch' => null,
            'team' => null,
        ];

        $labels = [];
        foreach ($targets as $target) {
            $type = strtolower((string) ($target['target_type'] ?? ''));
            $value = (string) ($target['target_value'] ?? '');
            $label = (string) ($target['target_label'] ?? $value);
            $labels[] = $label !== '' ? $label : $value;

            if ($type === 'employee' && $summary['employee_id'] === null) {
                $summary['type'] = 'employee';
                $summary['employee_id'] = (int) $value;
                $summary['employee_name'] = $label;
            } elseif ($type === 'department' && $summary['department'] === null) {
                $summary['department'] = $value;
                if ($summary['type'] === 'company') {
                    $summary['type'] = 'department';
                }
            } elseif ($type === 'branch' && $summary['branch'] === null) {
                $summary['branch'] = $value;
                if ($summary['type'] === 'company') {
                    $summary['type'] = 'branch';
                }
            } elseif ($type === 'team' && $summary['team'] === null) {
                $summary['team'] = $value;
                if ($summary['type'] === 'company') {
                    $summary['type'] = 'team';
                }
            }
        }

        $summary['label'] = implode(', ', array_filter($labels));
        if ($summary['label'] === '') {
            $summary['label'] = 'Assigned';
        }

        return $summary;
    }

    private function loadTargets(array $eventIds): array
    {
        if (empty($eventIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT event_id, target_type, target_value, target_label
             FROM tbl_calendar_event_targets
             WHERE event_id IN ($placeholders)
             ORDER BY event_id ASC, id ASC"
        );
        $stmt->execute(array_values($eventIds));

        $targets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $targets[(int) $row['event_id']][] = $row;
        }

        return $targets;
    }

    private function syncTargets(int $eventId, array $targets): void
    {
        $deleteStmt = $this->db->prepare('DELETE FROM tbl_calendar_event_targets WHERE event_id = ?');
        $deleteStmt->execute([$eventId]);

        if (empty($targets)) {
            return;
        }

        $insertStmt = $this->db->prepare(
            'INSERT INTO tbl_calendar_event_targets (event_id, target_type, target_value, target_label, created_at)
             VALUES (:event_id, :target_type, :target_value, :target_label, :created_at)'
        );

        $now = date('Y-m-d H:i:s');
        foreach ($targets as $target) {
            if (empty($target['type']) || empty($target['value'])) {
                continue;
            }

            $insertStmt->execute([
                ':event_id' => $eventId,
                ':target_type' => strtolower(trim((string) $target['type'])),
                ':target_value' => trim((string) $target['value']),
                ':target_label' => trim((string) ($target['label'] ?? '')),
                ':created_at' => $now,
            ]);
        }
    }

    private function normalizePayload(array $data, bool $requireTitle = true): array
    {
        $payload = [];
        if ($requireTitle || array_key_exists('title', $data)) {
            $payload['title'] = trim((string) ($data['title'] ?? ''));
            if ($requireTitle && $payload['title'] === '') {
                throw new \InvalidArgumentException('Title is required.');
            }
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = trim((string) $data['description']);
        }

        if ($requireTitle || array_key_exists('event_type', $data)) {
            $payload['event_type'] = strtolower(trim((string) ($data['event_type'] ?? '')));
            if ($payload['event_type'] !== '' && !in_array($payload['event_type'], self::EVENT_TYPES, true)) {
                throw new \InvalidArgumentException('Invalid event type.');
            }
            if ($requireTitle && $payload['event_type'] === '') {
                throw new \InvalidArgumentException('Event type is required.');
            }
        }

        if ($requireTitle || array_key_exists('status', $data)) {
            $status = strtolower(trim((string) $data['status']));
            if (!in_array($status, self::STATUSES, true)) {
                $status = 'pending';
            }
            $payload['status'] = $status;
        }

        if (array_key_exists('start_at', $data) || array_key_exists('start', $data) || $requireTitle) {
            $payload['start_at'] = $this->normalizeDateTime((string) ($data['start_at'] ?? $data['start'] ?? ''));
            if ($payload['start_at'] === null) {
                throw new \InvalidArgumentException('Start date/time is required.');
            }
        }
        if (array_key_exists('end_at', $data) || array_key_exists('end', $data) || $requireTitle) {
            $payload['end_at'] = $this->normalizeDateTime((string) ($data['end_at'] ?? $data['end'] ?? ''));
            if ($payload['end_at'] === null) {
                throw new \InvalidArgumentException('End date/time is required.');
            }
        }
        if (isset($payload['start_at'], $payload['end_at']) && strtotime($payload['end_at']) < strtotime($payload['start_at'])) {
            throw new \InvalidArgumentException('End cannot be before start.');
        }

        if (array_key_exists('all_day', $data)) {
            $payload['all_day'] = !empty($data['all_day']) ? 1 : 0;
        }

        if (array_key_exists('recurrence', $data) || array_key_exists('recurrence_rule', $data)) {
            $payload['recurrence_rule'] = $this->normalizeRecurrenceRule($data['recurrence'] ?? $data['recurrence_rule'] ?? null);
        }

        foreach (['created_by', 'updated_by', 'approved_by', 'rejected_by', 'cancelled_by'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field] === '' ? null : (int) $data[$field];
            }
        }

        foreach (['approved_at', 'rejected_at', 'cancelled_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = !empty($data[$field]) ? $this->normalizeDateTime((string) $data[$field]) : null;
            }
        }

        if (array_key_exists('targets', $data)) {
            $payload['targets'] = $this->normalizeTargets($data['targets']);
        }

        return $payload;
    }

    private function normalizeTargets($targets): array
    {
        if (!is_array($targets)) {
            return [];
        }

        $normalized = [];
        foreach ($targets as $target) {
            if (!is_array($target)) {
                continue;
            }

            $type = strtolower(trim((string) ($target['type'] ?? '')));
            $value = trim((string) ($target['value'] ?? ''));
            if ($type === '' || $value === '') {
                continue;
            }

            $normalized[] = [
                'type' => $type,
                'value' => $value,
                'label' => trim((string) ($target['label'] ?? '')),
            ];
        }

        return $normalized;
    }

    private function normalizeRecurrenceRule($recurrence): ?string
    {
        if ($recurrence === null || $recurrence === '') {
            return null;
        }

        if (is_string($recurrence)) {
            $decoded = json_decode($recurrence, true);
            if (is_array($decoded)) {
                $recurrence = $decoded;
            } else {
                return null;
            }
        }

        if (!is_array($recurrence)) {
            return null;
        }

        $frequency = strtolower(trim((string) ($recurrence['frequency'] ?? 'none')));
        if (!in_array($frequency, ['none', 'daily', 'weekly', 'monthly', 'yearly'], true)) {
            $frequency = 'none';
        }

        if ($frequency === 'none') {
            return null;
        }

        $rule = [
            'frequency' => $frequency,
            'interval' => max(1, (int) ($recurrence['interval'] ?? 1)),
        ];

        if (!empty($recurrence['until'])) {
            $rule['until'] = substr((string) $recurrence['until'], 0, 10);
        }

        if (!empty($recurrence['days']) && is_array($recurrence['days'])) {
            $rule['days'] = array_values(array_filter(array_map(static fn ($day) => strtolower(trim((string) $day)), $recurrence['days'])));
        }

        return json_encode($rule, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $dt = \DateTimeImmutable::createFromFormat($format, $value);
            if ($dt instanceof \DateTimeImmutable) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function matchesFilters(array $event, array $filters): bool
    {
        if (!empty($filters['event_type']) && strtolower((string) $filters['event_type']) !== strtolower((string) $event['event_type'])) {
            return false;
        }

        if (!empty($filters['status']) && strtolower((string) $filters['status']) !== strtolower((string) $event['status'])) {
            return false;
        }

        if (!empty($filters['employee_id'])) {
            $employeeId = (int) $filters['employee_id'];
            $matchesEmployee = (int) ($event['employee_id'] ?? 0) === $employeeId;
            $matchesCompany = (($event['scope']['type'] ?? '') === 'company');
            if (!$matchesEmployee && !$matchesCompany) {
                return false;
            }
        }

        if (!empty($filters['department'])) {
            $department = (string) $filters['department'];
            $eventDepartment = (string) ($event['department'] ?? '');
            $matchesCompany = (($event['scope']['type'] ?? '') === 'company');
            if ($eventDepartment !== $department && !$matchesCompany) {
                return false;
            }
        }

        if (!empty($filters['branch'])) {
            $branch = (string) $filters['branch'];
            $eventBranch = (string) ($event['branch'] ?? '');
            $matchesCompany = (($event['scope']['type'] ?? '') === 'company');
            if ($eventBranch !== $branch && !$matchesCompany) {
                return false;
            }
        }

        return true;
    }

    private function buildSummary(array $events): array
    {
        $summary = [
            'total' => count($events),
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'cancelled' => 0,
            'leave' => 0,
            'holiday' => 0,
            'shift' => 0,
            'meeting' => 0,
            'reminder' => 0,
            'task' => 0,
        ];

        foreach ($events as $event) {
            $status = strtolower((string) ($event['status'] ?? ''));
            $type = strtolower((string) ($event['event_type'] ?? ''));
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
            if (isset($summary[$type])) {
                $summary[$type]++;
            }
        }

        return $summary;
    }

    private function getEmployees(): array
    {
        $columns = 'id, full_name, department';
        if ($this->employeeHasColumn('branch')) {
            $columns .= ', branch';
        }

        $stmt = $this->db->query(
            "SELECT {$columns}
             FROM tbl_employees
             WHERE deleted_at IS NULL
             ORDER BY full_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDistinctColumnValues(string $column): array
    {
        if (!$this->employeeHasColumn($column)) {
            return [];
        }

        $stmt = $this->db->query(
            "SELECT DISTINCT {$column} AS value
             FROM tbl_employees
             WHERE {$column} IS NOT NULL
               AND {$column} <> ''
               AND deleted_at IS NULL
             ORDER BY {$column} ASC"
        );

        return array_values(array_filter(array_map(static fn ($row) => $row['value'] ?? null, $stmt->fetchAll(PDO::FETCH_ASSOC))));
    }

    private function getRawByUuid(string $uuid): ?array
    {
        $this->assertSchemaReady();
        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_calendar_events WHERE uuid = :uuid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':uuid' => $uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function employeeHasColumn(string $column): bool
    {
        if ($this->employeeColumns === null) {
            $this->employeeColumns = [];
            $stmt = $this->db->query('SHOW COLUMNS FROM tbl_employees');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                if (!empty($col['Field'])) {
                    $this->employeeColumns[$col['Field']] = true;
                }
            }
        }

        return isset($this->employeeColumns[$column]);
    }

    private function assertSchemaReady(): void
    {
        if ($this->calendarTableExists()) {
            return;
        }

        throw new \RuntimeException('Calendar tables are missing. Run the calendar migration before using this module.');
    }

    private function calendarTableExists(): bool
    {
        if ($this->calendarColumns === null) {
            $this->calendarColumns = [];
            try {
                $stmt = $this->db->query('SHOW COLUMNS FROM tbl_calendar_events');
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                    if (!empty($col['Field'])) {
                        $this->calendarColumns[$col['Field']] = true;
                    }
                }
            } catch (PDOException $e) {
                return false;
            }
        }

        return !empty($this->calendarColumns);
    }

}
