<?php
declare(strict_types=1);

namespace App\Tests\Feature;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Feature tests for Leave approval workflow.
 */
class LeaveApprovalTest extends WebTestCase
{
    public function testLeaveApprovalWorkflow(): void
    {
        $client = static::createClient();

        // 1. Create a leave application
        $client->request('POST', '/api/leaves', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'employee_id' => 1,
            'leave_type_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'reason' => 'Summer vacation'
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $uuid = $data['data']['uuid'];

        // 2. Approve the leave
        $client->request('PATCH', "/api/leaves/{$uuid}/approve");
        $this->assertResponseIsSuccessful();

        // 3. Verify audit log exists
        // (In a real test, we would query the database or mock service)
        
        // 4. Verify calendar event exists
        // (Verify via API or DB)
    }
}
