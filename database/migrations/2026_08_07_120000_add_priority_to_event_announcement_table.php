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
        Schema::table('event_announcement', function (Blueprint $table) {
            if (!Schema::hasColumn('event_announcement', 'priority')) {
                $table->integer('priority')->default(0)->after('is_pinned');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_announcement', function (Blueprint $table) {
            if (Schema::hasColumn('event_announcement', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
