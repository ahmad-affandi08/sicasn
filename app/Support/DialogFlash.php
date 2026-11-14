<?php

declare(strict_types=1);

namespace App\Support;

final class DialogFlash
{
    public static function sukses(string $judul, string $pesan): array
    {
        return self::buat('sukses', $judul, $pesan);
    }

    public static function gagal(string $judul, string $pesan): array
    {
        return self::buat('gagal', $judul, $pesan);
    }

    public static function galat(string $judul, string $pesan): array
    {
        return self::buat('galat', $judul, $pesan);
    }

    public static function peringatan(string $judul, string $pesan, ?string $tautanWhatsapp = null): array
    {
        return self::buat('peringatan', $judul, $pesan, $tautanWhatsapp);
    }

    public static function info(string $judul, string $pesan): array
    {
        return self::buat('info', $judul, $pesan);
    }

    private static function buat(string $jenis, string $judul, string $pesan, ?string $tautanWhatsapp = null): array
    {
        return array_filter([
            'jenis' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'tautan' => $tautanWhatsapp === null ? null : ['label' => 'Hubungi admin via WhatsApp', 'url' => $tautanWhatsapp],
        ], fn ($nilai) => $nilai !== null);
    }
}
