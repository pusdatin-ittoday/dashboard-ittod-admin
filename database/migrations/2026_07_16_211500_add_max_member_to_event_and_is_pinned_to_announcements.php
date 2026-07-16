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
        Schema::table('event', function (Blueprint $table) {
            $table->integer('max_member')->default(3)->after('max_noncompetition_participant');
        });

        Schema::table('event_announcement', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn('max_member');
        });

        Schema::table('event_announcement', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
    }
};
