<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class CreateTblEmployeesTable extends AbstractMigration
{
    public function up(): void
    {
        // Let Phinx create the default 'id' column (auto-increment primary key)
        $table = $this->table('tbl_employees');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('photo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('username', 'string', ['limit' => 50]) // username
            ->addColumn('password', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 50])
            ->addColumn('last_name', 'string', ['limit' => 50])
            ->addColumn('full_name', 'string', ['limit' => 100])
            ->addColumn('gender', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('address', 'text', ['null' => true])
            ->addColumn('dob', 'date', ['null' => true])
            ->addColumn('position', 'string', ['limit' => 50])
            ->addColumn('department', 'string', ['limit' => 50])
            ->addColumn('date_hired', 'date')
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['username'], ['unique' => true])
            ->create();

        // Insert default employee
        $employeeUsername = trim((string) ($_ENV['HRMS_INITIAL_EMPLOYEE_USERNAME'] ?? getenv('HRMS_INITIAL_EMPLOYEE_USERNAME') ?? ''));
        $employeePassword = (string) ($_ENV['HRMS_INITIAL_EMPLOYEE_PASSWORD'] ?? getenv('HRMS_INITIAL_EMPLOYEE_PASSWORD') ?? '');
        $employeeEmail = trim((string) ($_ENV['HRMS_INITIAL_EMPLOYEE_EMAIL'] ?? getenv('HRMS_INITIAL_EMPLOYEE_EMAIL') ?? ''));
        $employeeFullName = trim((string) ($_ENV['HRMS_INITIAL_EMPLOYEE_FULL_NAME'] ?? getenv('HRMS_INITIAL_EMPLOYEE_FULL_NAME') ?? ''));

        if ($employeeUsername === '' || $employeePassword === '') {
            throw new RuntimeException(
                'HRMS initial employee credentials must be set in the environment before running this migration.'
            );
        }

        $employeeData = [
            [
                'uuid' => Uuid::v4(),
                'username' => $employeeUsername,
                'password' => password_hash($employeePassword, PASSWORD_BCRYPT),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'full_name' => $employeeFullName !== '' ? $employeeFullName : 'Admin User',
                'email' => $employeeEmail !== '' ? $employeeEmail : null,
                'position' => 'System Administrator',
                'department' => 'IT',
                'date_hired' => date('Y-m-d'),
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1,
            ]
        ];

        $this->table('tbl_employees')->insert($employeeData)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_employees')->drop()->save();
    }
}
