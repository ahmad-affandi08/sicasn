<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statistik_pelamars', function (Blueprint $table) {
            $table->id();
            $table->char('kunci', 40)->unique();
            $table->unsignedSmallInteger('tahun');
            $table->string('jenis_pengadaan', 20)->default('cpns');
            $table->string('cakupan', 10);
            $table->foreignId('instansi_id')->nullable()->constrained('instansis');
            $table->unsignedInteger('jumlah_formasi')->nullable();
            $table->unsignedInteger('jumlah_pelamar')->nullable();
            $table->unsignedInteger('jumlah_submit')->nullable();
            $table->unsignedInteger('jumlah_lolos_administrasi')->nullable();
            $table->date('tanggal_snapshot');
            $table->string('status_angka', 10);
            $table->char('tingkat_data', 1);
            $table->string('keyakinan', 10);
            $table->string('sumber_nama');
            $table->string('sumber_url', 500)->nullable();
            $table->string('catatan')->nullable();
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches');
            $table->timestamps();
            $table->index(['tahun', 'jenis_pengadaan', 'cakupan', 'instansi_id', 'tanggal_snapshot'], 'statistik_pelamars_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistik_pelamars');
    }
};
