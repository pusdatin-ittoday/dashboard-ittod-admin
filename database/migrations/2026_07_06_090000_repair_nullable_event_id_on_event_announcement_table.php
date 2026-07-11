<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair databases where the previous nullable migration was recorded
     * without changing the physical event_id column.
     */
    public function up(): void
    {
        if (! Schema::hasTable('event_announcement')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `event_announcement` MODIFY `event_id` VARCHAR(191) NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('event_announcement')) {
            return;
        }

        $hasGeneralAnnouncements = DB::table('event_announcement')
            ->whereNull('event_id')
            ->exists();

        if (! $hasGeneralAnnouncements) {
            DB::statement(
                'ALTER TABLE `event_announcement` MODIFY `event_id` VARCHAR(191) NOT NULL'
            );
        }
    }
};
