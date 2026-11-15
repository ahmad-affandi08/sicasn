<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instansis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_normal')->unique();
            $table->string('tipe', 10)->default('unknown')->index();
            $table->unsignedBigInteger('provinsi_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('wilayahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->nullable();
            $table->string('nama');
            $table->string('nama_normal');
            $table->string('tingkat', 15);
            $table->unsignedBigInteger('provinsi_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['tingkat', 'nama_normal']);
        });

        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->default('');
            $table->string('nama');
            $table->string('nama_normal');
            $table->timestamps();
            $table->unique(['kode', 'nama_normal']);
        });

        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_normal')->unique();
            $table->timestamps();
        });

        Schema::create('kualifikasi_sets', function (Blueprint $table) {
            $table->id();
            $table->char('hash', 40)->unique();
            $table->text('teks');
            $table->timestamps();
        });

        Schema::create('kualifikasi_set_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kualifikasi_set_id')->constrained('kualifikasi_sets')->cascadeOnDelete();
            $table->string('jenjang', 20);
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusans');
            $table->boolean('semua_jurusan')->default(false);
            $table->index(['jenjang', 'jurusan_id']);
        });

        Schema::create('persyaratans', function (Blueprint $table) {
            $table->id();
            $table->char('hash', 40)->unique();
            $table->text('uraian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persyaratans');
        Schema::dropIfExists('kualifikasi_set_items');
        Schema::dropIfExists('kualifikasi_sets');
        Schema::dropIfExists('jurusans');
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('wilayahs');
        Schema::dropIfExists('instansis');
    }
};
