<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\PayrollService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ControllerPayroll extends BaseController
{
    public function __construct(
        private readonly PayrollService $service
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $input = $this->readInput($request);
        $month = (int)($input['month'] ?? date('n'));
        $year = (int)($input['year'] ?? date('Y'));

        $result = $this->service->generateMonthlyPayroll($month, $year);

        if ($result['success']) {
            return $this->json([
                'success' => true,
                'message' => 'Payroll generated successfully',
                'period_id' => $result['period_id']
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to generate payroll'
        ], 400);
    }

    public function summary(Request $request): JsonResponse
    {
        $month = (int)$request->query->get('month', date('n'));
        $year = (int)$request->query->get('year', date('Y'));

        $data = $this->service->getPayrollSummary($month, $year);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function approve(Request $request): JsonResponse
    {
        $input = $this->readInput($request);
        $periodId = (int)($input['period_id'] ?? 0);

        if (!$periodId) {
            return $this->json(['success' => false, 'message' => 'Period ID is required'], 422);
        }

        $result = $this->service->approvePayroll($periodId);

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Payroll approved successfully']);
        }

        return $this->json(['success' => false, 'message' => 'Failed to approve payroll'], 400);
    }

    public function getConfig(Request $request, ?string $employeeId = null): JsonResponse
    {
        $employeeId = (int)($employeeId ?? $request->query->get('employee_id'));
        if (!$employeeId) {
            return $this->json(['success' => false, 'message' => 'Employee ID is required'], 422);
        }

        $config = $this->service->getSalaryConfig($employeeId);
        
        return $this->json([
            'success' => true,
            'data' => $config
        ]);
    }

    public function updateConfig(Request $request, ?string $employeeId = null): JsonResponse
    {
        $input = $this->readInput($request);
        $employeeId = (int)($employeeId ?? ($input['employee_id'] ?? 0));

        if (!$employeeId) {
            return $this->json(['success' => false, 'message' => 'Employee ID is required'], 422);
        }

        $result = $this->service->saveSalaryConfig($employeeId, $input);

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Salary configuration updated successfully']);
        }

        return $this->json(['success' => false, 'message' => 'Failed to update salary configuration'], 400);
    }

    private function readInput(Request $request): array
    {
        $content = $request->getContent();
        if (!$content) {
            return $request->request->all();
        }
        return json_decode($content, true) ?? [];
    }
}
