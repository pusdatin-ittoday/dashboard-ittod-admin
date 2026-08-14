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
        if (!Schema::hasTable('user_feedback')) {
            Schema::create('user_feedback', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('user_id');
                $table->string('subject');
                $table->text('content');
                $table->json('media_urls')->nullable();
                $table->string('status', 50)->default('pending');
                $table->timestamps();

                $table->foreign('user_id', 'user_feedback_user_id_foreign')
                      ->references('id')
                      ->on('user')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
