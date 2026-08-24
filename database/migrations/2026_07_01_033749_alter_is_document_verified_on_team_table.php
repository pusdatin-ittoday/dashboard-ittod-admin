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
            $table->renameColumn('is_document_verified', 'is_document_verified_old');
        });

        // 2. Create new enum column
        Schema::table('team', function (Blueprint $table) {
            $table->enum('is_document_verified', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('is_document_verified_old');
        });

        // 3. Migrate data
        DB::statement("
            UPDATE team
            SET is_document_verified = CASE
                WHEN is_document_verified_old = 1 THEN 'approved'
                WHEN is_document_verified_old = 0 AND verification_error IS NOT NULL AND verification_error != '' THEN 'rejected'
                ELSE 'pending'
            END
        ");

        // 4. Drop the old column
        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_document_verified_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE team CHANGE is_document_verified is_document_verified_new ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");

        Schema::table('team', function (Blueprint $table) {
            $table->tinyInteger('is_document_verified')->default(0)->after('is_document_verified_new');
        });

        DB::statement("
            UPDATE team
            SET is_document_verified = CASE
                WHEN is_document_verified_new = 'approved' THEN 1
                ELSE 0
            END
        ");

        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_document_verified_new');
        });
    }
};
