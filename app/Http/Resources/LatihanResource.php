<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Latihan\Application\BerandaLatihan;
use Src\Latihan\Application\HasilKategori;
use Src\Latihan\Application\HasilPercobaan;
use Src\Latihan\Application\ItemRiwayat;
use Src\Latihan\Application\PercobaanBerjalan;
use Src\Latihan\Application\SoalDikerjakan;
use Src\Latihan\Application\SoalHasil;
use Src\Latihan\Domain\ModeLatihan;

final class LatihanResource
{
    public static function beranda(BerandaLatihan $beranda): array
    {
        return [
            'ketersediaan' => $beranda->ketersediaan,
            'percobaanBerjalanId' => $beranda->percobaanBerjalanId,
            'ambang' => $beranda->ambang,
            'mode' => array_map(fn (ModeLatihan $mode) => ['nilai' => $mode->value, 'label' => $mode->label(), 'komposisi' => $mode->komposisi()], ModeLatihan::cases()),
            'riwayat' => array_map(fn (ItemRiwayat $item) => (array) $item, $beranda->riwayat),
        ];
    }

    public static function percobaan(PercobaanBerjalan $percobaan): array
    {
        return [
            'id' => $percobaan->id,
            'mode' => $percobaan->mode,
            'modeLabel' => ModeLatihan::from($percobaan->mode)->label(),
            'sisaDetik' => $percobaan->sisaDetik,
            'durasiDetik' => $percobaan->durasiDetik,
            'soal' => array_map(fn (SoalDikerjakan $soal) => (array) $soal, $percobaan->soal),
        ];
    }

    public static function hasil(HasilPercobaan $hasil): array
    {
        return [
            'id' => $hasil->id,
            'mode' => $hasil->mode,
            'modeLabel' => $hasil->modeLabel,
            'mulai' => $hasil->mulai,
            'selesai' => $hasil->selesai,
            'total' => $hasil->total,
            'totalMaks' => $hasil->totalMaks,
            'lulus' => $hasil->lulus,
            'kategori' => array_map(fn (HasilKategori $kategori) => (array) $kategori, $hasil->kategori),
            'soal' => array_map(fn (SoalHasil $soal) => (array) $soal, $hasil->soal),
        ];
    }
}
