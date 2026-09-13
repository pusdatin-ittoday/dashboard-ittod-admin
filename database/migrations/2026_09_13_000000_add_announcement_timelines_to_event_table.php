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
        if (Schema::hasTable('event')) {
            Schema::table('event', function (Blueprint $table) {
                if (!Schema::hasColumn('event', 'finalist_timeline_id')) {
                    $table->string('finalist_timeline_id', 36)->nullable()->after('whatsapp_group_link');
                }
                if (!Schema::hasColumn('event', 'winner_timeline_id')) {
                    $table->string('winner_timeline_id', 36)->nullable()->after('finalist_timeline_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('event')) {
            Schema::table('event', function (Blueprint $table) {
                if (Schema::hasColumn('event', 'winner_timeline_id')) {
                    $table->dropColumn('winner_timeline_id');
                }
                if (Schema::hasColumn('event', 'finalist_timeline_id')) {
                    $table->dropColumn('finalist_timeline_id');
                }
            });
        }
    }
};
