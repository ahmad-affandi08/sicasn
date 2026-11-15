<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum JenisPengadaan: string
{
    case Cpns = 'cpns';
    case PppkGuru = 'pppk_guru';
    case PppkTeknis = 'pppk_teknis';
    case PppkNakes = 'pppk_nakes';
    case Lainnya = 'lainnya';

    public static function dariBkn(?string $teks): self
    {
        $nilai = mb_strtolower(trim((string) $teks));

        if (str_starts_with($nilai, 'cpns')) {
            return self::Cpns;
        }

        if (str_contains($nilai, 'pppk') || str_contains($nilai, 'p3k')) {
            return match (true) {
                str_contains($nilai, 'guru') => self::PppkGuru,
                str_contains($nilai, 'nakes'), str_contains($nilai, 'kesehatan') => self::PppkNakes,
                str_contains($nilai, 'teknis') => self::PppkTeknis,
                default => self::Lainnya,
            };
        }

        return self::Lainnya;
    }

    public function label(): string
    {
        return match ($this) {
            self::Cpns => 'CPNS',
            self::PppkGuru => 'PPPK Guru',
            self::PppkTeknis => 'PPPK Teknis',
            self::PppkNakes => 'PPPK Tenaga Kesehatan',
            self::Lainnya => 'Lainnya',
        };
    }
}
