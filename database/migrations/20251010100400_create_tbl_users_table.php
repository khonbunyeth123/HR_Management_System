<?php
declare(strict_types=1);
use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class CreateTblUsersTable extends AbstractMigration
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
            ->addColumn('login_session', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->addForeignKey('role_id', 'tbl_roles', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $adminUsername = trim((string) ($_ENV['HRMS_INITIAL_ADMIN_USERNAME'] ?? getenv('HRMS_INITIAL_ADMIN_USERNAME') ?? ''));
        $adminPassword = (string) ($_ENV['HRMS_INITIAL_ADMIN_PASSWORD'] ?? getenv('HRMS_INITIAL_ADMIN_PASSWORD') ?? '');
        $adminEmail = trim((string) ($_ENV['HRMS_INITIAL_ADMIN_EMAIL'] ?? getenv('HRMS_INITIAL_ADMIN_EMAIL') ?? ''));
        $adminFullName = trim((string) ($_ENV['HRMS_INITIAL_ADMIN_FULL_NAME'] ?? getenv('HRMS_INITIAL_ADMIN_FULL_NAME') ?? ''));

        if ($adminUsername === '' || $adminPassword === '') {
            throw new RuntimeException(
                'HRMS initial admin credentials must be set in the environment before running this migration.'
            );
        }

        $adminData = [
            [
                'uuid' => Uuid::v4(),
                'username' => $adminUsername,
                'password' => password_hash($adminPassword, PASSWORD_BCRYPT),
                'full_name' => $adminFullName !== '' ? $adminFullName : 'Administrator',
                'email' => $adminEmail !== '' ? $adminEmail : 'admin@example.com',
                'role_id' => 1,
                'status_id' => 1,
                'login_session' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ]
        ];

        $this->table('tbl_users')->insert($adminData)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_users')->drop()->save();
    }
}
