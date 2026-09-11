<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('competition_submission')) {
            // Clean up any empty strings or invalid values before altering to JSON
            DB::statement("UPDATE `competition_submission` SET `submission_object` = NULL WHERE `submission_object` = ''");

            // Alter submission_object to JSON
            DB::statement("ALTER TABLE `competition_submission` MODIFY COLUMN `submission_object` JSON NULL");

            // If legacy media_id column exists, make it nullable so it doesn't block inserts
            if (Schema::hasColumn('competition_submission', 'media_id')) {
                DB::statement("ALTER TABLE `competition_submission` MODIFY COLUMN `media_id` VARCHAR(191) NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('competition_submission')) {
            DB::statement("ALTER TABLE `competition_submission` MODIFY COLUMN `submission_object` LONGTEXT NULL");
        }
    }
};
