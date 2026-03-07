<?php

namespace App\Controllers\Api;

use App\Services\ReportService;
use App\Helpers\response;

class ControllerReport
{
    protected $service;

    public function __construct()
    {
        $this->service = new ReportService();
    }

    public function dailyList(): void
    {
        $date = $_GET['date'] ?? null;

        if (!$date) {
            response::json([
                'success' => false,
                'message' => 'Date is required'
            ], 400);
            return;
        }

        $data = $this->service->getDailyList($date);

        response::json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function summary(): void
    {
        $from = $_GET['from'] ?? null;
        $to   = $_GET['to'] ?? null;
        $dept = $_GET['department'] ?? null;

        if (!$from || !$to) {
            response::json([
                'success' => false,
                'message' => 'From and To dates are required'
            ], 400);
            return;
        }

        $data = $this->service->getSummary($from, $to, $dept);

        response::json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function detailedList(): void
    {
        $from   = $_GET['from']        ?? null;
        $to     = $_GET['to']          ?? null;
        $dept   = $_GET['department']  ?? null;
        $search = $_GET['search']      ?? null;
        $status = $_GET['status']      ?? null;

        if (!$from || !$to) {
            response::json([
                'success' => false,
                'message' => 'From and To dates are required'
            ], 400);
            return;
        }

        $data = $this->service->getDetailedAttendance(
            $from, $to, $dept, $search, $status
        );

        response::json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function topEmployees(): void
    {
        $from = $_GET['from'] ?? null;
        $to   = $_GET['to']   ?? null;

        if (!$from || !$to) {
            response::json([
                'success' => false,
                'message' => 'From and To dates are required'
            ], 400);
            return;
        }
        $data = $this->service->getTopEmployees($from, $to);
        response::json([
            'success' => true,
            'data'    => $data
        ]);
    }


}
