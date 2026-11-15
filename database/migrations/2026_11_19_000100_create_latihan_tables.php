<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soals', function (Blueprint $table): void {
            $table->id();
            $table->char('kode', 40)->unique();
            $table->char('hash_isi', 40);
            $table->string('kategori', 3);
            $table->string('subkategori', 60);
            $table->text('pertanyaan');
            $table->text('pembahasan');
            $table->string('sumber_nama', 200);
            $table->string('sumber_url', 500)->nullable();
            $table->string('lisensi', 100);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['kategori', 'aktif']);
        });

        Schema::create('soal_opsis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('soal_id')->constrained('soals')->cascadeOnDelete();
            $table->char('label', 1);
            $table->text('teks');
            $table->unsignedTinyInteger('skor');
            $table->timestamps();
            $table->unique(['soal_id', 'label']);
        });

        Schema::create('percobaans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('mode', 12);
            $table->timestamp('mulai_at');
            $table->timestamp('batas_selesai_at');
            $table->timestamp('selesai_at')->nullable();
            $table->unsignedSmallInteger('jumlah_twk')->default(0);
            $table->unsignedSmallInteger('jumlah_tiu')->default(0);
            $table->unsignedSmallInteger('jumlah_tkp')->default(0);
            $table->unsignedSmallInteger('skor_twk')->default(0);
            $table->unsignedSmallInteger('skor_tiu')->default(0);
            $table->unsignedSmallInteger('skor_tkp')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'selesai_at']);
        });

        Schema::create('percobaan_soals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('percobaan_id')->constrained('percobaans')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soals');
            $table->unsignedSmallInteger('urutan');
            $table->foreignId('opsi_id')->nullable()->constrained('soal_opsis')->nullOnDelete();
            $table->boolean('ragu')->default(false);
            $table->timestamps();
            $table->unique(['percobaan_id', 'soal_id']);
            $table->index(['percobaan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan_soals');
        Schema::dropIfExists('percobaans');
        Schema::dropIfExists('soal_opsis');
        Schema::dropIfExists('soals');
    }
};
