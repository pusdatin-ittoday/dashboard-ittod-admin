<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semnas_participant', function (Blueprint $table) {
            $table->string('user_id');
            $table->string('event_id');
            $table->primary(['user_id', 'event_id']);

            // Pertanyaan pendaftaran semnas
            $table->boolean('kenal_sentral_komputer')->default(false);
            $table->string('sumber_kenal_sentral')->nullable();
            $table->boolean('kenal_acer')->default(false);
            $table->boolean('kenal_nvidia')->default(false);
            $table->boolean('kenal_microsoft')->default(false);

            // Bukti follow IG Narsum (key R2 atau URL langsung)
            $table->string('ig_follow_proof_key', 500)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')
                ->references('id')->on('user')
                ->onDelete('cascade')
                ->name('semnas_participant_user_id_foreign');

            $table->foreign('event_id')
                ->references('id')->on('event')
                ->onDelete('cascade')
                ->name('semnas_participant_event_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semnas_participant');
    }
};
