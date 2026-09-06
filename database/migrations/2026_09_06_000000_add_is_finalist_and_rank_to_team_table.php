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
            if (!Schema::hasColumn('team', 'is_finalist')) {
                $table->boolean('is_finalist')->default(false)->after('is_verified');
            }
            if (!Schema::hasColumn('team', 'rank')) {
                $table->integer('rank')->nullable()->after('is_finalist');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            if (Schema::hasColumn('team', 'rank')) {
                $table->dropColumn('rank');
            }
            if (Schema::hasColumn('team', 'is_finalist')) {
                $table->dropColumn('is_finalist');
            }
        });
    }
};
