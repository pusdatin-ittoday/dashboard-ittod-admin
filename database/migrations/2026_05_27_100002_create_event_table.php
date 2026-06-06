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
        Schema::create('event', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->text('title');
            $table->text('description');
            $table->text('guide_book_url');
            $table->enum('type', ['competition', 'non_competition']);
            $table->integer('price')->default(0);
            $table->string('contact_person1', 191);
            $table->string('contact_person2', 191)->nullable();
            $table->integer('max_noncompetition_participant')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
