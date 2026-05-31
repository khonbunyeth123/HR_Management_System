<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Helpers\Response;
use App\Services\CalendarService;

class ControllerCalendar
{
    private CalendarService $service;

    public function __construct()
    {
        $this->service = new CalendarService();
    }

    public function index(): void
    {
        try {
            $start = (string) ($_GET['start'] ?? date('Y-m-01'));
            $end = (string) ($_GET['end'] ?? date('Y-m-t'));

            $filters = [
                'employee_id' => (int) ($_GET['employee_id'] ?? 0),
                'department' => (string) ($_GET['department'] ?? ''),
                'branch' => (string) ($_GET['branch'] ?? ''),
                'event_type' => (string) ($_GET['event_type'] ?? ''),
                'status' => (string) ($_GET['status'] ?? ''),
            ];

            Response::json([
                'success' => true,
                'message' => 'Calendar events retrieved',
                'data' => $this->service->list($filters, substr($start, 0, 10), substr($end, 0, 10)),
            ]);
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function filters(): void
    {
        try {
            Response::json([
                'success' => true,
                'message' => 'Calendar filters retrieved',
                'data' => $this->service->filters(),
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function show(string $uuid): void
    {
        try {
            $event = $this->service->getEvent($uuid);
            if (!$event) {
                Response::notFound('Calendar event not found');
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Calendar event retrieved',
                'data' => $event,
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function store(): void
    {
        try {
            $input = $this->readJsonBody();
            $result = $this->service->create($input);

            Response::created([
                'uuid' => $result['uuid'],
                'id' => $result['id'],
            ], 'Calendar event created');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function update(string $uuid): void
    {
        try {
            $input = $this->readJsonBody();
            $ok = $this->service->update($uuid, $input);

            if (!$ok) {
                Response::notFound('Calendar event not found');
                return;
            }

            Response::json(['success' => true, 'message' => 'Calendar event updated']);
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function destroy(string $uuid): void
    {
        try {
            $actorId = (int) ($_SESSION['user_id'] ?? 0);
            $ok = $this->service->delete($uuid, $actorId ?: null);

            if (!$ok) {
                Response::notFound('Calendar event not found');
                return;
            }

            Response::json(['success' => true, 'message' => 'Calendar event deleted']);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function approveLeave(string $uuid): void
    {
        try {
            $ok = $this->service->approveLeave($uuid);
            Response::json([
                'success' => $ok,
                'message' => $ok ? 'Leave request approved' : 'Unable to approve leave request',
            ], $ok ? 200 : 400);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function rejectLeave(string $uuid): void
    {
        try {
            $input = $this->readJsonBody();
            $remark = trim((string) ($input['remark'] ?? ''));

            if ($remark === '') {
                Response::validationError(['remark' => 'Reject reason is required.']);
                return;
            }

            $ok = $this->service->rejectLeave($uuid, $remark);
            Response::json([
                'success' => $ok,
                'message' => $ok ? 'Leave request rejected' : 'Unable to reject leave request',
            ], $ok ? 200 : 400);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
