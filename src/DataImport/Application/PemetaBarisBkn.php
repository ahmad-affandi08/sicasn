<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\BarisTidakValid;
use Src\Formasi\Application\FormasiBaru;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Lokasi;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\PenguraiKualifikasi;

final class PemetaBarisBkn
{
    private const MAKS_ID = 36;

    public static function petakan(array $baris): FormasiBaru
    {
        $teksKualifikasi = self::teksAtauNull($baris['nama_pendidikan'] ?? null);

        return new FormasiBaru(
            self::wajibId($baris, 'id'),
            self::wajibId($baris, 'event_id'),
            NamaNormal::rapikan(self::wajibTeks($baris, 'nama_event', 'Seleksi tanpa nama')),
            self::teksAtauNull($baris['rekrutmen_id'] ?? null),
            NamaNormal::rapikan(self::wajibTeks($baris, 'instansi')),
            NamaNormal::rapikan((string) ($baris['jabatan_kode'] ?? '')),
            NamaNormal::rapikan(self::wajibTeks($baris, 'nama_jabatan')),
            JenisPengadaan::dariBkn(self::wajibTeks($baris, 'jenis_pengadaan')),
            NamaNormal::dari(self::wajibTeks($baris, 'jenis_formasi', 'UMUM')),
            max(1, self::bilanganAtauNull($baris['periode'] ?? null) ?? 1),
            Lokasi::dari(self::wajibTeks($baris, 'lokasi_formasi')),
            self::kuota($baris),
            self::bilanganAtauNull($baris['usia_min'] ?? null),
            self::bilanganAtauNull($baris['usia_max'] ?? null),
            self::bilanganAtauNull($baris['penghasilan_min'] ?? null),
            self::bilanganAtauNull($baris['penghasilan_max'] ?? null),
            self::teksAtauNull($baris['jobdesc'] ?? null),
            self::booleanAtauNull($baris['is_disabilitas'] ?? null),
            $teksKualifikasi === null ? null : NamaNormal::rapikan($teksKualifikasi),
            PenguraiKualifikasi::urai($teksKualifikasi),
            self::persyaratan($baris['persyaratan'] ?? null),
        );
    }

    private static function wajibId(array $baris, string $kolom): string
    {
        $nilai = self::wajibTeks($baris, $kolom);

        if (mb_strlen($nilai) > self::MAKS_ID) {
            throw new BarisTidakValid($kolom, "Kolom {$kolom} lebih panjang dari ".self::MAKS_ID.' karakter.');
        }

        return $nilai;
    }

    private static function wajibTeks(array $baris, string $kolom, ?string $bawaan = null): string
    {
        $nilai = $baris[$kolom] ?? null;

        if (is_string($nilai) && trim($nilai) !== '') {
            return trim($nilai);
        }

        if ($bawaan !== null) {
            return $bawaan;
        }

        throw new BarisTidakValid($kolom, "Kolom {$kolom} wajib terisi.");
    }

    private static function kuota(array $baris): int
    {
        $nilai = $baris['jumlah'] ?? null;

        if (! is_numeric($nilai) || (int) $nilai < 0) {
            throw new BarisTidakValid('jumlah', 'Kolom jumlah harus berupa angka nol atau lebih.');
        }

        return (int) $nilai;
    }

    private static function teksAtauNull(mixed $nilai): ?string
    {
        return is_string($nilai) && trim($nilai) !== '' ? trim($nilai) : null;
    }

    private static function bilanganAtauNull(mixed $nilai): ?int
    {
        return is_numeric($nilai) ? (int) $nilai : null;
    }

    private static function booleanAtauNull(mixed $nilai): ?bool
    {
        return match (true) {
            is_bool($nilai) => $nilai,
            $nilai === 1, $nilai === '1' => true,
            $nilai === 0, $nilai === '0' => false,
            default => null,
        };
    }

    private static function persyaratan(mixed $daftar): array
    {
        if (! is_array($daftar)) {
            return [];
        }

        $hasil = [];

        foreach ($daftar as $butir) {
            $uraian = is_array($butir) ? NamaNormal::rapikan($butir['nama'] ?? null) : '';

            if ($uraian !== '') {
                $hasil[] = ['uraian' => $uraian, 'wajib' => (bool) ($butir['mandatory'] ?? true)];
            }
        }

        return $hasil;
    }
}
