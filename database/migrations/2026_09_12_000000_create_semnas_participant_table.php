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
        if (!Schema::hasTable('semnas_participant')) {
            Schema::create('semnas_participant', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('user_id');
                $table->string('event_id');
                $table->boolean('kenal_sentral_komputer')->default(false);
                $table->string('sumber_kenal_sentral', 255)->nullable();
                $table->boolean('kenal_acer')->default(false);
                $table->boolean('kenal_nvidia')->default(false);
                $table->boolean('kenal_microsoft')->default(false);
                $table->string('ig_follow_proof_key', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['user_id', 'event_id'], 'semnas_participant_user_event_unique');
                $table->foreign('user_id', 'semnas_participant_user_id_foreign')->references('id')->on('user')->onDelete('cascade');
                $table->foreign('event_id', 'semnas_participant_event_id_foreign')->references('id')->on('event')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semnas_participant');
    }
};
