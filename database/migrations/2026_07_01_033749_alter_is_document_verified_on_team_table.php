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
        // 1. Add temporary column to store the mapped enum values
        Schema::table('team', function (Blueprint $table) {
            $table->enum('new_is_document_verified', ['pending', 'approved', 'rejected'])->default('pending')->after('is_document_verified');
        });

        // 2. Map existing data
        // 0 with verification_error -> 'rejected'
        DB::table('team')
            ->where('is_document_verified', 0)
            ->whereNotNull('verification_error')
            ->where('verification_error', '!=', '')
            ->update(['new_is_document_verified' => 'rejected']);
            
        // 0 without verification_error -> 'pending' (already default, but let's be explicit)
        DB::table('team')
            ->where('is_document_verified', 0)
            ->where(function($query) {
                $query->whereNull('verification_error')
                      ->orWhere('verification_error', '');
            })
            ->update(['new_is_document_verified' => 'pending']);

        // 1 -> 'approved'
        DB::table('team')
            ->where('is_document_verified', 1)
            ->update(['new_is_document_verified' => 'approved']);

        // 3. Drop the old column
        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_document_verified');
        });

        // 4. Rename new column to old name
        Schema::table('team', function (Blueprint $table) {
            $table->renameColumn('new_is_document_verified', 'is_document_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->tinyInteger('old_is_document_verified')->default(0)->after('is_document_verified');
        });

        DB::table('team')
            ->where('is_document_verified', 'approved')
            ->update(['old_is_document_verified' => 1]);

        DB::table('team')
            ->whereIn('is_document_verified', ['pending', 'rejected'])
            ->update(['old_is_document_verified' => 0]);

        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn('is_document_verified');
        });

        Schema::table('team', function (Blueprint $table) {
            $table->renameColumn('old_is_document_verified', 'is_document_verified');
        });
    }
};
