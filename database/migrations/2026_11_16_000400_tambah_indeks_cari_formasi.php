<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formasis', function (Blueprint $table) {
            $table->index(['jabatan_id', 'status'], 'formasis_cari_jabatan');
            $table->index(['instansi_id', 'status'], 'formasis_cari_instansi');
        });
    }

    public function down(): void
    {
        Schema::table('formasis', function (Blueprint $table) {
            $table->dropIndex('formasis_cari_jabatan');
            $table->dropIndex('formasis_cari_instansi');
        });
    }
};
