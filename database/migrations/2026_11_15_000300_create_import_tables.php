<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 20)->index();
            $table->string('status', 15)->index();
            $table->timestamp('mulai_at');
            $table->timestamp('selesai_at')->nullable();
            $table->json('ringkasan')->nullable();
            $table->string('sumber')->nullable();
            $table->char('hash_respons', 64)->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('baris');
            $table->string('kolom')->nullable();
            $table->string('pesan');
            $table->text('nilai')->nullable();
        });

        Schema::create('event_terdeteksis', function (Blueprint $table) {
            $table->id();
            $table->char('event_id', 36)->unique();
            $table->string('nama_event');
            $table->string('jenis_pengadaan_teramati');
            $table->unsignedInteger('jumlah_baris');
            $table->boolean('diabaikan');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('diberitahukan_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_terdeteksis');
        Schema::dropIfExists('import_errors');
        Schema::dropIfExists('import_batches');
    }
};
