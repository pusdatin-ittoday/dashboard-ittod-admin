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
        if (!Schema::hasColumn('team', 'is_name_changed')) {
            Schema::table('team', function (Blueprint $table) {
                $table->boolean('is_name_changed')->default(false)->after('is_verified');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('team', 'is_name_changed')) {
            Schema::table('team', function (Blueprint $table) {
                $table->dropColumn('is_name_changed');
            });
        }
    }
};
