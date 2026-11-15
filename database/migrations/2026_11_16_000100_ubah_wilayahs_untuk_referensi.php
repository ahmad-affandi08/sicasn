<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $yatim = DB::table('wilayahs')->whereNull('kode')->pluck('id')->all();

        foreach (array_chunk($yatim, 500) as $potongan) {
            DB::table('formasis')->whereIn('wilayah_id', $potongan)->update(['wilayah_id' => null, 'provinsi_id' => null]);
            DB::table('wilayahs')->whereIn('id', $potongan)->delete();
        }

        Schema::table('wilayahs', function (Blueprint $table) {
            $table->unique('kode');
        });
    }

    public function down(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            $table->dropUnique(['kode']);
        });
    }
};
