<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRealScanDatetimeAndStatusToAttendance extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_attendance_records');

        if (!$table->hasColumn('scan_datetime')) {
            $table->addColumn('scan_datetime', 'datetime', ['null' => true, 'after' => 'date'])->update();
        }

        if (!$table->hasColumn('status')) {
            $table->addColumn('status', 'string', ['limit' => 30, 'null' => true, 'after' => 'check_type_id'])->update();
        }

        $this->execute("
            UPDATE tbl_attendance_records
            SET scan_datetime = CONCAT(date, ' ', check_time)
            WHERE scan_datetime IS NULL
        ");

        $this->execute("
            UPDATE tbl_attendance_records a
            JOIN tbl_check_types ct ON ct.id = a.check_type_id
            SET a.status = CASE
                WHEN ct.name IN ('Check-in 1', 'Check-in 2') AND TIME(a.scan_datetime) > ct.standard_time THEN 'Late'
                WHEN ct.name = 'Check-out 1' AND TIME(a.scan_datetime) < ct.standard_time THEN 'Early Leave'
                WHEN ct.name = 'Check-out 2' AND TIME(a.scan_datetime) < ct.standard_time THEN 'Early Leave'
                WHEN ct.name = 'Check-out 2' AND TIME(a.scan_datetime) > ct.standard_time THEN 'Overtime'
                ELSE 'On Time'
            END
            WHERE a.scan_datetime IS NOT NULL
              AND a.deleted_at IS NULL
              AND a.check_type_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        $table = $this->table('tbl_attendance_records');

        if ($table->hasColumn('status')) {
            $table->removeColumn('status')->update();
        }

        if ($table->hasColumn('scan_datetime')) {
            $table->removeColumn('scan_datetime')->update();
        }
    }
}
