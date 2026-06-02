<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarEvent;

class CalendarService
{
    public function __construct(
        private readonly CalendarEvent $model,
        private readonly NotificationService $notificationService,
        private readonly LeaveService $leaveService
    ) {}

    public function list(array $filters, string $start, string $end): array
    {
        return $this->model->list($filters, $start, $end);
    }

    public function filters(): array
    {
        return $this->model->filters();
    }

    public function getEvent(string $uuid): ?array
    {
        $event = $this->model->getByUuid($uuid);
        if (!$event) {
            return null;
        }

        $event['targets'] = $this->model->getTargetsByUuid($uuid);
        return $event;
    }

    public function create(array $input): array
    {
        $this->validateWritePayload($input);
        $result = $this->model->create($input);
        $event = $this->getEvent($result['uuid']);
        if ($event) {
            $this->notifyTargets($event, 'created');
        }
        return $result;
    }

    public function update(string $uuid, array $input): bool
    {
        $this->validateWritePayload($input, false);
        $ok = $this->model->update($uuid, $input);
        if ($ok) {
            $event = $this->getEvent($uuid);
            if ($event) {
                $this->notifyTargets($event, 'updated');
            }
        }
        return $ok;
    }

    public function delete(string $uuid, ?int $deletedBy = null): bool
    {
        $event = $this->getEvent($uuid);
        $ok = $this->model->delete($uuid, $deletedBy);
        if ($ok && $event) {
            $this->notifyTargets($event, 'cancelled');
        }
        return $ok;
    }

    public function approveLeave(string $uuid, string $remark = ''): bool
    {
        return $this->leaveService->approveLeave($uuid);
    }

    public function rejectLeave(string $uuid, string $remark): bool
    {
        return $this->leaveService->rejectLeave($uuid, $remark);
    }

    public function updateStatus(string $uuid, string $status, ?int $actorId = null): bool
    {
        return $this->model->updateStatus($uuid, $status, $actorId);
    }

    private function validateWritePayload(array $input, bool $requireTitle = true): void
    {
        if ($requireTitle && empty(trim((string) ($input['title'] ?? '')))) {
            throw new \InvalidArgumentException('Title is required.');
        }

        if (empty($input['event_type'])) {
            throw new \InvalidArgumentException('Event type is required.');
        }

        if (empty($input['start_at']) && empty($input['start'])) {
            throw new \InvalidArgumentException('Start date/time is required.');
        }

        if (empty($input['end_at']) && empty($input['end'])) {
            throw new \InvalidArgumentException('End date/time is required.');
        }
    }

    private function notifyTargets(array $event, string $action): void
    {
        $title = match ($action) {
            'created' => 'New calendar event',
            'updated' => 'Calendar event updated',
            'cancelled' => 'Calendar event cancelled',
            default => 'Calendar update',
        };

        $body = sprintf('%s: %s', ucfirst($action), (string) ($event['title'] ?? 'Calendar event'));

        $recipients = $this->resolveRecipients($event);
        if (empty($recipients)) {
            return;
        }

        foreach ($recipients as $employeeId) {
            $this->notificationService->sendCalendarEventNotification(
                (int) $employeeId,
                $title,
                $body,
                [
                    'event_uuid' => (string) ($event['uuid'] ?? ''),
                    'event_type' => (string) ($event['event_type'] ?? ''),
                    'status' => (string) ($event['status'] ?? ''),
                    'action' => $action,
                ]
            );
        }
    }

    private function resolveRecipients(array $event): array
    {
        $recipients = [];
        $employees = $this->filters()['employees'] ?? [];

        foreach (($event['targets'] ?? []) as $target) {
            $type = strtolower((string) ($target['target_type'] ?? ''));
            $value = (string) ($target['target_value'] ?? '');

            if ($type === 'employee' && $value !== '') {
                $recipients[] = (int) $value;
            } elseif ($type === 'department' && $value !== '') {
                foreach ($employees as $employee) {
                    if (($employee['department'] ?? null) === $value && !empty($employee['id'])) {
                        $recipients[] = (int) $employee['id'];
                    }
                }
            } elseif ($type === 'branch' && $value !== '') {
                foreach ($employees as $employee) {
                    if (($employee['branch'] ?? null) === $value && !empty($employee['id'])) {
                        $recipients[] = (int) $employee['id'];
                    }
                }
            }
        }

        if (empty($recipients)) {
            foreach ($employees as $employee) {
                if (!empty($employee['id'])) {
                    $recipients[] = (int) $employee['id'];
                }
            }
        }

        return array_values(array_unique(array_filter($recipients)));
    }
}
