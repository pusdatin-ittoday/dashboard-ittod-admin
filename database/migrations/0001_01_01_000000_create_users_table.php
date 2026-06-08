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
        Schema::create('user', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->string('email', 191)->unique();
            $table->string('full_name', 191);
            $table->dateTime('birth_date', 3)->nullable();
            $table->enum('pendidikan', ['sma', 's1', 'd3', 'd4'])->nullable();
            $table->string('nama_sekolah', 191)->nullable();
            $table->string('entry_source', 191)->nullable();
            $table->string('phone_number', 191)->nullable();
            $table->string('id_line', 191)->nullable();
            $table->string('id_discord', 191)->nullable();
            $table->string('id_instagram', 191)->nullable();
            $table->tinyInteger('is_registration_complete')->default(0);
            $table->enum('jenis_kelamin', ['laki2', 'perempuan'])->nullable();
            $table->string('ktm_key', 191)->nullable();
            $table->string('twibbon_key', 191)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->nullable();
        });

        Schema::create('user_identity', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->string('email', 191)->unique();
            $table->enum('provider', ['google', 'basic', 'github'])->default('basic');
            $table->string('hash', 191)->nullable();
            $table->tinyInteger('is_verified')->default(0);
            $table->string('verification_token', 191)->nullable();
            $table->dateTime('verification_token_expiration', 3)->nullable();
            $table->string('password_recovery_token', 191)->nullable();
            $table->dateTime('password_recovery_token_expiration', 3)->nullable();
            $table->string('refresh_token', 191)->nullable();
            $table->enum('role', ['superadmin', 'admin_keuangan', 'panitia', 'user'])->default('user');
            $table->rememberToken();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->nullable();

            $table->foreign('id')->references('id')->on('user')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token', 191);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->string('user_id', 191)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_identity');
        Schema::dropIfExists('user');
    }
};
