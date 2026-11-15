<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formasis', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique();
            $table->char('event_id', 36)->index();
            $table->string('nama_event');
            $table->char('rekrutmen_id', 36)->nullable();
            $table->foreignId('instansi_id')->constrained('instansis');
            $table->foreignId('jabatan_id')->constrained('jabatans');
            $table->string('jenis_pengadaan', 20);
            $table->string('jenis_formasi', 40);
            $table->unsignedTinyInteger('periode')->default(1);
            $table->string('lokasi_teks');
            $table->string('tipe_lokasi', 20);
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayahs');
            $table->unsignedBigInteger('provinsi_id')->nullable()->index();
            $table->unsignedInteger('kuota');
            $table->unsignedSmallInteger('usia_min')->nullable();
            $table->unsignedSmallInteger('usia_max')->nullable();
            $table->unsignedBigInteger('penghasilan_min')->nullable();
            $table->unsignedBigInteger('penghasilan_max')->nullable();
            $table->text('jobdesc')->nullable();
            $table->boolean('is_disabilitas')->nullable();
            $table->foreignId('kualifikasi_set_id')->nullable()->constrained('kualifikasi_sets');
            $table->boolean('kualifikasi_tersedia')->default(false);
            $table->char('hash_baris', 40);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at')->index();
            $table->string('status', 10)->default('aktif');
            $table->timestamps();
            $table->index(['status', 'jenis_pengadaan', 'instansi_id']);
        });

        Schema::create('formasi_persyaratans', function (Blueprint $table) {
            $table->foreignId('formasi_id')->constrained('formasis')->cascadeOnDelete();
            $table->foreignId('persyaratan_id')->constrained('persyaratans');
            $table->boolean('wajib')->default(true);
            $table->unsignedSmallInteger('urutan');
            $table->primary(['formasi_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formasi_persyaratans');
        Schema::dropIfExists('formasis');
    }
};
