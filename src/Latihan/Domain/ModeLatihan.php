<?php

declare(strict_types=1);

namespace Src\Latihan\Domain;

enum ModeLatihan: string
{
    case Simulasi = 'simulasi';
    case Twk = 'twk';
    case Tiu = 'tiu';
    case Tkp = 'tkp';

    private const JUMLAH_LATIHAN_KATEGORI = 20;

    private const DETIK_PER_SOAL_LATIHAN = 90;

    private const DETIK_PER_SOAL_SIMULASI = 100 * 60 / 110;

    public function label(): string
    {
        return match ($this) {
            self::Simulasi => 'Simulasi SKD lengkap',
            self::Twk => 'Latihan TWK',
            self::Tiu => 'Latihan TIU',
            self::Tkp => 'Latihan TKP',
        };
    }

    public function komposisi(): array
    {
        return match ($this) {
            self::Simulasi => [
                KategoriSoal::Twk->value => KategoriSoal::Twk->jumlahResmi(),
                KategoriSoal::Tiu->value => KategoriSoal::Tiu->jumlahResmi(),
                KategoriSoal::Tkp->value => KategoriSoal::Tkp->jumlahResmi(),
            ],
            default => [$this->value => self::JUMLAH_LATIHAN_KATEGORI],
        };
    }

    public function durasiDetik(int $jumlahSoal): int
    {
        $perSoal = $this === self::Simulasi ? self::DETIK_PER_SOAL_SIMULASI : self::DETIK_PER_SOAL_LATIHAN;

        return (int) ceil($jumlahSoal * $perSoal);
    }
}
