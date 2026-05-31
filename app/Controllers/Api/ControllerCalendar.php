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
    }

    public function filters(): void
    {
        Response::json([
            'success' => true,
            'message' => 'Calendar filters retrieved',
            'data' => $this->service->filters(),
        ]);
    }

    public function show(string $uuid): void
    {
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
    }

    public function store(): void
    {
        $input = $this->readJsonBody();
        $result = $this->service->create($input);

        Response::created([
            'uuid' => $result['uuid'],
            'id' => $result['id'],
        ], 'Calendar event created');
    }

    public function update(string $uuid): void
    {
        $input = $this->readJsonBody();
        $ok = $this->service->update($uuid, $input);

        if (!$ok) {
            Response::notFound('Calendar event not found');
            return;
        }

        Response::json(['success' => true, 'message' => 'Calendar event updated']);
    }

    public function destroy(string $uuid): void
    {
        $actorId = (int) ($_SESSION['user_id'] ?? 0);
        $ok = $this->service->delete($uuid, $actorId ?: null);

        if (!$ok) {
            Response::notFound('Calendar event not found');
            return;
        }

        Response::json(['success' => true, 'message' => 'Calendar event deleted']);
    }

    public function approveLeave(string $uuid): void
    {
        $ok = $this->service->approveLeave($uuid);
        Response::json([
            'success' => $ok,
            'message' => $ok ? 'Leave request approved' : 'Unable to approve leave request',
        ], $ok ? 200 : 400);
    }

    public function rejectLeave(string $uuid): void
    {
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
