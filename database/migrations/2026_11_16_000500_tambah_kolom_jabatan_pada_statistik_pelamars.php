<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statistik_pelamars', function (Blueprint $table) {
            $table->string('kode_jabatan', 40)->nullable();
            $table->string('nama_jabatan')->nullable();
            $table->string('jabatan_normal')->nullable();
            $table->string('lokasi_teks')->nullable();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayahs');
            $table->index(['instansi_id', 'jabatan_normal'], 'statistik_pelamars_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('statistik_pelamars', function (Blueprint $table) {
            $table->dropIndex('statistik_pelamars_jabatan');
            $table->dropConstrainedForeignId('wilayah_id');
            $table->dropColumn(['kode_jabatan', 'nama_jabatan', 'jabatan_normal', 'lokasi_teks']);
        });
    }
};
