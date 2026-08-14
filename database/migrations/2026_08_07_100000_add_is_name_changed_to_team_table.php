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
        Schema::table('team', function (Blueprint $table) {
            if (!Schema::hasColumn('team', 'is_name_changed')) {
                $table->boolean('is_name_changed')->default(false)->after('is_verified');
            }
            if (!Schema::hasColumn('team', 'previous_team_name')) {
                $table->text('previous_team_name')->nullable()->after('is_name_changed');
            }
            if (!Schema::hasColumn('team', 'name_changed_at')) {
                $table->dateTime('name_changed_at', 3)->nullable()->after('previous_team_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            if (Schema::hasColumn('team', 'name_changed_at')) {
                $table->dropColumn('name_changed_at');
            }
            if (Schema::hasColumn('team', 'previous_team_name')) {
                $table->dropColumn('previous_team_name');
            }
            if (Schema::hasColumn('team', 'is_name_changed')) {
                $table->dropColumn('is_name_changed');
            }
        });
    }
};
