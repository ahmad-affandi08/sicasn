<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peluang_instansis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instansi_id')->unique()->constrained('instansis')->cascadeOnDelete();
            $table->unsignedBigInteger('pelamar')->nullable();
            $table->unsignedBigInteger('submit')->nullable();
            $table->unsignedBigInteger('formasi')->nullable();
            $table->string('formasi_dari_tanggal', 10)->nullable();
            $table->string('status_angka', 10);
            $table->char('tingkat_data', 1);
            $table->string('keyakinan', 10);
            $table->date('tanggal_snapshot');
            $table->string('sumber_nama');
            $table->string('sumber_url', 500)->nullable();
            $table->decimal('rasio', 14, 4)->nullable();
            $table->unsignedTinyInteger('golongan_rasio');
            $table->unsignedBigInteger('pelamar_dasar')->nullable();
            $table->unsignedTinyInteger('golongan_pelamar');
            $table->timestamps();
        });

        Schema::table('formasis', function (Blueprint $table) {
            $table->index(['status', 'jenis_pengadaan', 'kuota'], 'formasis_urut_kuota');
            $table->index(['status', 'jenis_pengadaan', 'first_seen_at'], 'formasis_urut_terbaru');
            $table->index(['status', 'jenis_pengadaan', 'provinsi_id'], 'formasis_filter_provinsi');
            $table->index(['status', 'jenis_pengadaan', 'wilayah_id'], 'formasis_filter_wilayah');
        });
    }

    public function down(): void
    {
        Schema::table('formasis', function (Blueprint $table) {
            $table->dropIndex('formasis_urut_kuota');
            $table->dropIndex('formasis_urut_terbaru');
            $table->dropIndex('formasis_filter_provinsi');
            $table->dropIndex('formasis_filter_wilayah');
        });

        Schema::dropIfExists('peluang_instansis');
    }
};
