<?php

declare(strict_types=1);

use App\Support\Uuid;
use Phinx\Migration\AbstractMigration;

final class SeedCambodiaPublicHolidays2026 extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('tbl_calendar_events')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $events = [
            ['2026-01-01', '2026-01-01', 'Internaand tional New Year Day'],
            ['2026-01-07', '2026-01-07', 'Day of Victory over the Genocidal Regime'],
            ['2026-03-08', '2026-03-08', 'International Women\'s Rights Day'],
            ['2026-04-14', '2026-04-16', 'Khmer New Year\'s Day'],
            ['2026-05-01', '2026-05-01', 'International Labor Day'],
            ['2026-05-05', '2026-05-05', 'Royal Ploughing Ceremony'],
            ['2026-05-14', '2026-05-14', 'Birthday of His Majesty King Norodom Sihamoni'],
            ['2026-06-18', '2026-06-18', 'Birthday of Her Majesty the Queen-Mother Norodom Monineath Sihanouk'],
            ['2026-09-24', '2026-09-24', 'Constitution Day'],
            ['2026-10-10', '2026-10-12', 'Pchum Ben Day'],
            ['2026-10-15', '2026-10-15', 'Mourning Day of the Late King-Father Norodom Sihanouk'],
            ['2026-10-29', '2026-10-29', 'Coronation Day of King Norodom Sihamoni'],
            ['2026-11-09', '2026-11-09', 'National Independence Day'],
            ['2026-11-23', '2026-11-25', 'Water Festival'],
            ['2026-12-29', '2026-12-29', 'Peace Day in Cambodia'],
        ];

        foreach ($events as [$startDate, $endDate, $title]) {
            $safeTitle = addslashes($title);
            $safeStartAt = $startDate . ' 00:00:00';
            $safeEndAt = $endDate . ' 23:59:59';
            $existing = $this->fetchRow(
                "SELECT id FROM tbl_calendar_events
                 WHERE title = '{$safeTitle}'
                   AND start_at = '{$safeStartAt}'
                   AND end_at = '{$safeEndAt}'
                   AND deleted_at IS NULL
                 LIMIT 1"
            );

            if ($existing) {
                continue;
            }

            $this->execute(
                'INSERT INTO tbl_calendar_events
                    (uuid, title, description, event_type, status, start_at, end_at, all_day, created_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    Uuid::v4(),
                    $title,
                    'Cambodia public holiday for 2026',
                    'holiday',
                    'approved',
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59',
                    1,
                    $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('tbl_calendar_events')) {
            return;
        }

        $titles = [
            'International New Year Day',
            'Day of Victory over the Genocidal Regime',
            'International Women\'s Rights Day',
            'Khmer New Year\'s Day',
            'International Labor Day',
            'Royal Ploughing Ceremony',
            'Birthday of His Majesty King Norodom Sihamoni',
            'Birthday of Her Majesty the Queen-Mother Norodom Monineath Sihanouk',
            'Constitution Day',
            'Pchum Ben Day',
            'Mourning Day of the Late King-Father Norodom Sihanouk',
            'Coronation Day of King Norodom Sihamoni',
            'National Independence Day',
            'Water Festival',
            'Peace Day in Cambodia',
        ];

        $placeholders = implode(', ', array_fill(0, count($titles), '?'));
        $this->execute(
            "DELETE FROM tbl_calendar_events
             WHERE event_type = 'holiday'
               AND title IN ({$placeholders})
               AND description = 'Cambodia public holiday for 2026'",
            $titles
        );
    }
}
