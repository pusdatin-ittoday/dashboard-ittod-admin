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

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('event_announcement', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('event_id')->nullable()->change();
            });
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
            if (DB::getDriverName() === 'sqlite') {
                Schema::table('event_announcement', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('event_id')->nullable(false)->change();
                });
                return;
            }

            DB::statement(
                'ALTER TABLE `event_announcement` MODIFY `event_id` VARCHAR(191) NOT NULL'
            );
        }
    }
};
