<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\ReferensiInstansi;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\TipeInstansi;

final class EloquentReferensiInstansi implements ReferensiInstansi
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function pastikan(array $namaInstansi): array
    {
        $butuh = [];

        foreach ($namaInstansi as $nama) {
            $butuh[NamaNormal::dari($nama)] = NamaNormal::rapikan($nama);
        }

        $hasil = [];

        foreach (array_chunk($butuh, 300, true) as $potongan) {
            $hasil += DB::table('instansis')->whereIn('nama_normal', array_keys($potongan))->pluck('id', 'nama_normal')->all();
        }

        $belum = array_diff_key($butuh, $hasil);
        $waktu = Date::now()->format(self::FORMAT);

        foreach (array_chunk($belum, 300, true) as $potongan) {
            DB::table('instansis')->insertOrIgnore(array_map(fn (string $nama, string $kunci) => [
                'nama' => mb_substr($nama, 0, 255),
                'nama_normal' => $kunci,
                'tipe' => TipeInstansi::dariNama($nama)->value,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ], $potongan, array_keys($potongan)));

            $hasil += DB::table('instansis')->whereIn('nama_normal', array_keys($potongan))->pluck('id', 'nama_normal')->all();
        }

        return $hasil;
    }
}
