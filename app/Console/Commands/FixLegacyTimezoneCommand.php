<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixLegacyTimezoneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-legacy-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shift existing legacy UTC database timestamps by +7 hours to GMT+7 (Asia/Jakarta)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Shifting existing legacy database timestamps by +7 hours...');

        $driver = DB::getDriverName();

        $updateTable = function (string $table, array $columns) use ($driver) {
            if (!Schema::hasTable($table)) {
                return;
            }

            $validCols = array_filter($columns, fn($col) => Schema::hasColumn($table, $col));
            if (empty($validCols)) {
                return;
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $setClauses = [];
                foreach ($validCols as $col) {
                    $setClauses[] = "`{$col}` = IF(`{$col}` IS NOT NULL, DATE_ADD(`{$col}`, INTERVAL 7 HOUR), NULL)";
                }
                $sql = "UPDATE `{$table}` SET " . implode(', ', $setClauses);
                DB::statement($sql);
            } elseif ($driver === 'sqlite') {
                $setClauses = [];
                foreach ($validCols as $col) {
                    $setClauses[] = "`{$col}` = CASE WHEN `{$col}` IS NOT NULL THEN datetime(`{$col}`, '+7 hours') ELSE NULL END";
                }
                $sql = "UPDATE `{$table}` SET " . implode(', ', $setClauses);
                DB::statement($sql);
            }
        };

        $updateTable('event_announcement', ['created_at', 'updated_at']);
        $updateTable('event_timeline', ['date', 'end_date', 'created_at', 'updated_at']);
        $updateTable('competition_timeline', ['start_date', 'end_date', 'created_at', 'updated_at']);
        $updateTable('team', ['created_at', 'updated_at']);
        $updateTable('team_member', ['created_at', 'updated_at']);
        $updateTable('user', ['created_at', 'updated_at', 'last_read_announcements_at']);
        $updateTable('user_identity', ['created_at', 'updated_at']);
        $updateTable('event_participant', ['date_added', 'created_at', 'updated_at']);
        $updateTable('competition_submission', ['created_at', 'updated_at']);
        $updateTable('media', ['created_at', 'updated_at']);

        $this->info('Legacy timestamps updated successfully.');
        return 0;
    }
}
