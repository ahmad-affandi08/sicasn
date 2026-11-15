<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final class PenguraiKualifikasi
{
    private const POLA_JENJANG = '/^(?:SD|SMP|(?:SMA|SMK|MA|SLTA)(?:\/(?:SMA|SMK|MA))*|SLTP|D-?(?:I{1,3}|IV|[1-4])|S-?[1-3]|PROFESI|PROF|SPESIALIS|SUBSPESIALIS)$/iu';

    public static function urai(?string $teks): array
    {
        $teks = NamaNormal::rapikan($teks);

        if ($teks === '') {
            return [];
        }

        $butir = [];

        foreach (preg_split('/\s+\/\s+/u', $teks) ?: [] as $potongan) {
            [$kepala, $sisa] = array_pad(explode(' ', trim($potongan), 2), 2, '');

            if (preg_match(self::POLA_JENJANG, $kepala) === 1) {
                $butir[] = [self::normalkanJenjang($kepala), NamaNormal::dari($sisa)];

                continue;
            }

            if ($butir === []) {
                $butir[] = ['LAINNYA', NamaNormal::dari($potongan)];

                continue;
            }

            $terakhir = array_key_last($butir);
            $butir[$terakhir][1] = trim($butir[$terakhir][1].' / '.NamaNormal::dari($potongan), ' /');
        }

        $hasil = [];

        foreach ($butir as [$jenjang, $jurusan]) {
            $semua = $jurusan === '' || $jurusan === 'SEMUA JURUSAN';
            $kualifikasi = new Kualifikasi($jenjang, $semua ? null : $jurusan, $semua);
            $hasil[$kualifikasi->kunci()] = $kualifikasi;
        }

        return array_values($hasil);
    }

    private static function normalkanJenjang(string $mentah): string
    {
        $kode = mb_strtoupper(preg_replace('/[^A-Za-z0-9\/]/', '', $mentah) ?? '');

        return match (true) {
            in_array($kode, ['DI', 'D1'], true) => 'D1',
            in_array($kode, ['DII', 'D2'], true) => 'D2',
            in_array($kode, ['DIII', 'D3'], true) => 'D3',
            in_array($kode, ['DIV', 'D4'], true) => 'D4',
            str_starts_with($kode, 'SMA'), str_starts_with($kode, 'SMK'), str_starts_with($kode, 'MA'), $kode === 'SLTA' => 'SMA',
            $kode === 'SLTP' => 'SMP',
            $kode === 'PROF' => 'PROFESI',
            default => $kode,
        };
    }
}
