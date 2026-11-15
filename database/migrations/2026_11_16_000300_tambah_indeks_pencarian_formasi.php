<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kualifikasi_set_items', function (Blueprint $table) {
            $table->index(['kualifikasi_set_id', 'jenjang', 'jurusan_id'], 'kualifikasi_items_set_jenjang');
        });

        Schema::table('formasis', function (Blueprint $table) {
            $table->index(['status', 'jenis_pengadaan', 'kualifikasi_set_id'], 'formasis_filter_kualifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('formasis', function (Blueprint $table) {
            $table->dropIndex('formasis_filter_kualifikasi');
        });

        Schema::table('kualifikasi_set_items', function (Blueprint $table) {
            $table->dropIndex('kualifikasi_items_set_jenjang');
        });
    }
};
