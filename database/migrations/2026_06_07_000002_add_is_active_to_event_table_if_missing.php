<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('event', 'is_active')) {
            Schema::table('event', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('event', 'is_active')) {
            Schema::table('event', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
