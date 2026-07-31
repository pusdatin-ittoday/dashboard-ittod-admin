<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Shift existing UTC timestamps/dateTimes by +7 hours to align with Asia/Jakarta (WIB / GMT+7).
     */
    public function up(): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if (Schema::hasTable('event_announcement') && Schema::hasColumn('event_announcement', 'created_at')) {
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement("UPDATE `event_announcement` SET `created_at` = DATE_SUB(`created_at`, INTERVAL 7 HOUR), `updated_at` = DATE_SUB(`updated_at`, INTERVAL 7 HOUR) WHERE `created_at` IS NOT NULL");
            } elseif ($driver === 'sqlite') {
                DB::statement("UPDATE `event_announcement` SET `created_at` = datetime(`created_at`, '-7 hours'), `updated_at` = datetime(`updated_at`, '-7 hours') WHERE `created_at` IS NOT NULL");
            }
        }
    }
};
