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
        if (Schema::hasTable('team') && !Schema::hasColumn('team', 'has_bootcamp_addon')) {
            Schema::table('team', function (Blueprint $table) {
                $table->boolean('has_bootcamp_addon')->default(false)->after('verification_error');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('team') && Schema::hasColumn('team', 'has_bootcamp_addon')) {
            Schema::table('team', function (Blueprint $table) {
                $table->dropColumn('has_bootcamp_addon');
            });
        }
    }
};
