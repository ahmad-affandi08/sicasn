<?php

declare(strict_types=1);

namespace Src\Latihan\Domain;

enum KategoriSoal: string
{
    case Twk = 'twk';
    case Tiu = 'tiu';
    case Tkp = 'tkp';

    public const SKOR_BENAR = 5;

    public function label(): string
    {
        return match ($this) {
            self::Twk => 'Tes Wawasan Kebangsaan',
            self::Tiu => 'Tes Intelegensia Umum',
            self::Tkp => 'Tes Karakteristik Pribadi',
        };
    }

    public function jumlahResmi(): int
    {
        return match ($this) {
            self::Twk => 30,
            self::Tiu => 35,
            self::Tkp => 45,
        };
    }

    public function skorMaksResmi(): int
    {
        return $this->jumlahResmi() * self::SKOR_BENAR;
    }

    public function berkunciTunggal(): bool
    {
        return $this !== self::Tkp;
    }
}
