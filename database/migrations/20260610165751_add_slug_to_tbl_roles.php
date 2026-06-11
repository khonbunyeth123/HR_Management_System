<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSlugToTblRoles extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_roles');
        if (!$table->hasColumn('slug')) {
            $table->addColumn('slug', 'string', ['limit' => 50, 'after' => 'name', 'null' => true])
                  ->addIndex(['slug'], ['unique' => true])
                  ->update();
            
            // Populate existing slugs from names
            $rows = $this->fetchAll('SELECT id, name FROM tbl_roles');
            $pdo = $this->getAdapter()->getConnection();
            foreach ($rows as $row) {
                $slug = strtolower(trim($row['name']));
                $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                $slug = preg_replace('/^-|-$/', '', $slug);
                $this->execute("UPDATE tbl_roles SET slug = " . $pdo->quote($slug) . " WHERE id = " . $row['id']);
            }

            // Make slug NOT NULL after populating
            $table->changeColumn('slug', 'string', ['limit' => 50, 'null' => false])
                  ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_roles');
        if ($table->hasColumn('slug')) {
            $table->removeColumn('slug')->update();
        }
    }
}
