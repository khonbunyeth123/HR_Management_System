-- FIX 1: Timezone Support
CREATE TABLE IF NOT EXISTS tbl_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` VARCHAR(255) NOT NULL
);

INSERT INTO tbl_settings (`key`, `value`) VALUES ('timezone', 'UTC');

-- FIX 5: Grace Period Configuration
ALTER TABLE tbl_check_types 
ADD COLUMN grace_minutes_before INT DEFAULT 5,
ADD COLUMN grace_minutes_after INT DEFAULT 15;

-- FIX 7 & 8: Attendance Table Updates
ALTER TABLE tbl_attendance_records
ADD COLUMN created_by INT NULL,
ADD COLUMN updated_by INT NULL,
ADD COLUMN updated_at DATETIME NULL,
ADD COLUMN is_excused BOOLEAN DEFAULT FALSE,
ADD COLUMN excuse_note TEXT NULL;

-- FIX 4: Daily Limit Enforced at DB Level (Already has a unique index, will need to make sure)
-- The current migration 20251010102627_create_attendance_records_table.php already has:
-- ->addIndex(['employee_id', 'date', 'check_type_id'], ['unique' => true])
-- This should be enough for FIX 4.

-- FIX 6: Admin Manual Override
CREATE TABLE tbl_attendance_corrections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_id INT NOT NULL,
    corrected_by INT NOT NULL,
    original_value TIME NOT NULL,
    new_value TIME NOT NULL,
    reason TEXT NOT NULL,
    corrected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_id) REFERENCES tbl_attendance_records(id)
);

-- FIX 9: Performance Index
CREATE INDEX idx_employee_date ON tbl_attendance_records(employee_id, date);
-- The unique index on (employee_id, date, check_type_id) already exists, so that's covered.
