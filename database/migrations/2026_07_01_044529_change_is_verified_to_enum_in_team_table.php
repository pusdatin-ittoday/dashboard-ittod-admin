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
        // 1. Rename old column
        Schema::table('team', function (Blueprint $table) {
            $table->renameColumn('is_verified', 'is_verified_old');
        });

        // 2. Create new enum column
        Schema::table('team', function (Blueprint $table) {
            $table->enum('is_verified', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('is_document_verified');
        });

        // 3. Migrate data
        // 1 -> 'approved'
        // 0 and verification_error is not null -> 'rejected'
        // else -> 'pending'
        DB::statement("
            UPDATE team
            SET is_verified = CASE
                WHEN is_verified_old = 1 THEN 'approved'
                WHEN is_verified_old = 0 AND verification_error IS NOT NULL THEN 'rejected'
                ELSE 'pending'
            END
        ");

        // 4. Drop old column
        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_verified_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->renameColumn('is_verified', 'is_verified_new');
        });

        Schema::table('team', function (Blueprint $table) {
            $table->tinyInteger('is_verified')->default(0)->after('is_document_verified');
        });

        DB::statement("
            UPDATE team
            SET is_verified = CASE
                WHEN is_verified_new = 'approved' THEN 1
                ELSE 0
            END
        ");

        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_verified_new');
        });
    }
};
