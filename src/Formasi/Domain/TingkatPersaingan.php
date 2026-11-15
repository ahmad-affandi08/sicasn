<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum TingkatPersaingan: string
{
    case Longgar = 'longgar';
    case Sedang = 'sedang';
    case Ketat = 'ketat';
    case BelumFinal = 'belum_final';
    case BelumAdaData = 'belum_ada_data';

    public function label(): string
    {
        return match ($this) {
            self::Longgar => 'Longgar',
            self::Sedang => 'Sedang',
            self::Ketat => 'Ketat',
            self::BelumFinal => 'Belum final',
            self::BelumAdaData => 'Belum ada data',
        };
    }
}
