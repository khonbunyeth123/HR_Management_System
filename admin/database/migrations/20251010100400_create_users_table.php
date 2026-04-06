<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_users');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('username', 'string', ['limit' => 50])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('full_name', 'string', ['limit' => 100])
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('role_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('login_session', 'string', ['limit' => 255, 'null' => true]) // added column
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        // Insert default admin + test users
        $users = [
            [
                'uuid' => bin2hex(random_bytes(16)), // generates unique UUID
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_BCRYPT), // default password
                'full_name' => 'Administrator',
                'email' => 'admin@example.com',
                'role_id' => 1,
                'status_id' => 1,
                'login_session' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'username' => 'testadmin',
                'password' => password_hash('Test1234', PASSWORD_BCRYPT),
                'full_name' => 'Test Admin',
                'email' => 'testadmin@example.com',
                'role_id' => 1,
                'status_id' => 1,
                'login_session' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'username' => 'testmanager',
                'password' => password_hash('Test1234', PASSWORD_BCRYPT),
                'full_name' => 'Test Manager',
                'email' => 'testmanager@example.com',
                'role_id' => 2,
                'status_id' => 1,
                'login_session' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'username' => 'testemployee',
                'password' => password_hash('Test1234', PASSWORD_BCRYPT),
                'full_name' => 'Test Employee',
                'email' => 'testemployee@example.com',
                'role_id' => 3,
                'status_id' => 1,
                'login_session' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
        ];

        $this->table('tbl_users')->insert($users)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_users')->drop()->save();
    }
}