<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class SeedEmployeesBatch extends AbstractMigration
{
    public function up(): void
    {
        $password = password_hash('123456', PASSWORD_BCRYPT);
        $createdAt = date('Y-m-d H:i:s');

        $employees = [
            ['john.doe', 'John', 'Doe', 'John Doe', 'john.doe@example.com', 'Developer', 'IT', '2026-01-01'],
            ['jane.smith', 'Jane', 'Smith', 'Jane Smith', 'jane.smith@example.com', 'Designer', 'Design', '2026-01-02'],
            ['robert.brown', 'Robert', 'Brown', 'Robert Brown', 'robert.brown@example.com', 'Manager', 'Sales', '2026-01-03'],
            ['emily.davis', 'Emily', 'Davis', 'Emily Davis', 'emily.davis@example.com', 'Analyst', 'Finance', '2026-01-04'],
            ['michael.wilson', 'Michael', 'Wilson', 'Michael Wilson', 'michael.wilson@example.com', 'Support', 'IT', '2026-01-05'],
            ['sarah.miller', 'Sarah', 'Miller', 'Sarah Miller', 'sarah.miller@example.com', 'Manager', 'Marketing', '2026-01-06'],
            ['david.taylor', 'David', 'Taylor', 'David Taylor', 'david.taylor@example.com', 'Sales Rep', 'Sales', '2026-01-07'],
            ['jessica.anderson', 'Jessica', 'Anderson', 'Jessica Anderson', 'jessica.anderson@example.com', 'HR', 'HR', '2026-01-08'],
            ['james.thomas', 'James', 'Thomas', 'James Thomas', 'james.thomas@example.com', 'Lead', 'IT', '2026-01-09'],
            ['linda.jackson', 'Linda', 'Jackson', 'Linda Jackson', 'linda.jackson@example.com', 'Coordinator', 'Marketing', '2026-01-10'],
            ['william.white', 'William', 'White', 'William White', 'william.white@example.com', 'Accountant', 'Finance', '2026-01-11'],
            ['barbara.harris', 'Barbara', 'Harris', 'Barbara Harris', 'barbara.harris@example.com', 'Designer', 'Design', '2026-01-12'],
            ['richard.martin', 'Richard', 'Martin', 'Richard Martin', 'richard.martin@example.com', 'Sales Rep', 'Sales', '2026-01-13'],
            ['susan.thompson', 'Susan', 'Thompson', 'Susan Thompson', 'susan.thompson@example.com', 'HR', 'HR', '2026-01-14'],
            ['joseph.garcia', 'Joseph', 'Garcia', 'Joseph Garcia', 'joseph.garcia@example.com', 'Developer', 'IT', '2026-01-15'],
            ['margaret.martinez', 'Margaret', 'Martinez', 'Margaret Martinez', 'margaret.martinez@example.com', 'Manager', 'Design', '2026-01-16'],
            ['charles.robinson', 'Charles', 'Robinson', 'Charles Robinson', 'charles.robinson@example.com', 'Analyst', 'Finance', '2026-01-17'],
            ['karen.clark', 'Karen', 'Clark', 'Karen Clark', 'karen.clark@example.com', 'Support', 'IT', '2026-01-18'],
            ['christopher.rodriguez', 'Christopher', 'Rodriguez', 'Christopher Rodriguez', 'christopher.rodriguez@example.com', 'Sales Rep', 'Sales', '2026-01-19'],
            ['nancy.lewis', 'Nancy', 'Lewis', 'Nancy Lewis', 'nancy.lewis@example.com', 'Marketing', 'Marketing', '2026-01-20'],
            ['daniel.lee', 'Daniel', 'Lee', 'Daniel Lee', 'daniel.lee@example.com', 'Developer', 'IT', '2026-01-21'],
            ['betty.walker', 'Betty', 'Walker', 'Betty Walker', 'betty.walker@example.com', 'Designer', 'Design', '2026-01-22'],
            ['paul.hall', 'Paul', 'Hall', 'Paul Hall', 'paul.hall@example.com', 'Manager', 'Sales', '2026-01-23'],
            ['helen.allen', 'Helen', 'Allen', 'Helen Allen', 'helen.allen@example.com', 'Analyst', 'Finance', '2026-01-24'],
            ['mark.young', 'Mark', 'Young', 'Mark Young', 'mark.young@example.com', 'Support', 'IT', '2026-01-25'],
        ];

        $data = [];
        foreach ($employees as $emp) {
            $data[] = [
                'uuid' => Uuid::v4(),
                'username' => $emp[0],
                'password' => $password,
                'first_name' => $emp[1],
                'last_name' => $emp[2],
                'full_name' => $emp[3],
                'email' => $emp[4],
                'phone' => '0' . rand(10000000, 99999999),
                'position' => $emp[5],
                'department' => $emp[6],
                'date_hired' => $emp[7],
                'status_id' => 1,
                'created_at' => $createdAt,
            ];
        }

        $this->table('tbl_employees')->insert($data)->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM tbl_employees WHERE username LIKE 'emp%' OR email LIKE '%@example.com'");
    }
}
