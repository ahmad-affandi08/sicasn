<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('peran', 20)->default('member')->index();
            $table->string('no_whatsapp', 20)->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamp('diaktifkan_at')->nullable();
            $table->timestamp('dinonaktifkan_at')->nullable();
            $table->string('alasan_nonaktif')->nullable();
            $table->string('catatan_pembayaran')->nullable();
            $table->boolean('wajib_ganti_password')->default(true);
            $table->timestamp('password_sementara_berlaku_sampai')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('sesi_aktif_id', 100)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
