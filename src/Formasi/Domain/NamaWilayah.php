<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final class NamaWilayah
{
    private const PREFIKS_REFERENSI = '/^(?:kabupaten|kab\.?|kota\s+administrasi|kota|provinsi|prov\.?)\s+/iu';

    public static function kunci(string $nama): string
    {
        $huruf = mb_strtolower(NamaNormal::rapikan($nama));
        $huruf = preg_replace('/^administrasi\s+/u', '', $huruf) ?? $huruf;
        $huruf = preg_replace('/^daerah\s+khusus\s+ibu\s?kota\b/u', 'dki', $huruf) ?? $huruf;
        $huruf = preg_replace('/^daerah\s+istimewa\b/u', 'di', $huruf) ?? $huruf;
        $huruf = preg_replace('/\bdan\b/u', ' ', $huruf) ?? $huruf;

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $huruf) ?? '';
    }

    public static function kunciReferensi(string $namaLengkap): string
    {
        return self::kunci(preg_replace(self::PREFIKS_REFERENSI, '', NamaNormal::rapikan($namaLengkap), 1) ?? $namaLengkap);
    }
}
