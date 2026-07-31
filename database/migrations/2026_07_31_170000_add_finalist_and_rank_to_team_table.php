<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->boolean('is_finalist')->default(false)->after('is_verified');
            $table->unsignedTinyInteger('rank')->nullable()->after('is_finalist')
                ->comment('Juara 1=1, 2=2, 3=3. Null jika bukan juara.');
        });
    }

    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn(['is_finalist', 'rank']);
        });
    }
};
