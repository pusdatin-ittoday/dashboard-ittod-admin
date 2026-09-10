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
        if (Schema::hasTable('event_timeline') && !Schema::hasColumn('event_timeline', 'is_submission')) {
            Schema::table('event_timeline', function (Blueprint $table) {
                $table->boolean('is_submission')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('event_timeline') && Schema::hasColumn('event_timeline', 'is_submission')) {
            Schema::table('event_timeline', function (Blueprint $table) {
                $table->dropColumn('is_submission');
            });
        }
    }
};
