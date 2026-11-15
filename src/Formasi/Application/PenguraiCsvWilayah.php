<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Exception\ReferensiWilayahRusak;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\TipeLokasi;

final class PenguraiCsvWilayah
{
    public static function urai(string $isi): array
    {
        $baris = preg_split('/\R/u', trim($isi)) ?: [];
        $header = str_getcsv((string) array_shift($baris), ',', '"', '');

        if ($header !== ['kode', 'nama', 'tingkat']) {
            throw ReferensiWilayahRusak::headerSalah();
        }

        $hasil = [];

        foreach ($baris as $nomor => $teks) {
            if (trim($teks) === '') {
                continue;
            }

            [$kode, $nama, $tingkat] = array_pad(str_getcsv($teks, ',', '"', ''), 3, '');
            $jenis = TipeLokasi::tryFrom((string) $tingkat);

            if (preg_match('/^\d{2}(\.\d{2})?$/', (string) $kode) !== 1 || NamaNormal::rapikan($nama) === '' || $jenis === null || $jenis === TipeLokasi::SatuanKerja) {
                throw ReferensiWilayahRusak::barisSalah($nomor + 2);
            }

            $hasil[] = new BarisWilayah((string) $kode, NamaNormal::rapikan($nama), $jenis);
        }

        return $hasil;
    }
}
