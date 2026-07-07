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
        Schema::table('competition_timeline', function (Blueprint $table) {
            // Already renamed in previous failed run
            if (!Schema::hasColumn('competition_timeline', 'start_date')) {
                $table->renameColumn('date', 'start_date');
            }
        });

        Schema::table('competition_timeline', function (Blueprint $table) {
            if (!Schema::hasColumn('competition_timeline', 'end_date')) {
                $table->dateTime('end_date', 3)->nullable()->after('start_date');
            }
        });

        Schema::table('event_timeline', function (Blueprint $table) {
            $table->dateTime('end_date', 3)->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_timeline', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });

        Schema::table('competition_timeline', function (Blueprint $table) {
            $table->dropColumn('end_date');
            $table->renameColumn('start_date', 'date');
        });
    }
};
