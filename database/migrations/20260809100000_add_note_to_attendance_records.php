<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNoteToAttendanceRecords extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_attendance_records');

        if (!$table->hasColumn('note')) {
            $table->addColumn('note', 'text', ['null' => true, 'after' => 'status'])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_attendance_records');

        if ($table->hasColumn('note')) {
            $table->removeColumn('note')->update();
        }
    }
}
