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
            $table->text('description')->nullable()->change();
            $table->text('guide_book_url')->nullable()->change();
            $table->string('contact_person1', 191)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->text('guide_book_url')->nullable(false)->change();
            $table->string('contact_person1', 191)->nullable(false)->change();
        });
    }
};
