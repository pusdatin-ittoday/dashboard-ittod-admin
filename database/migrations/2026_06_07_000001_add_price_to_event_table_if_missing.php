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
        if (! Schema::hasColumn('event', 'price')) {
            Schema::table('event', function (Blueprint $table) {
                $table->integer('price')->default(0)->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('event', 'price')) {
            Schema::table('event', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
