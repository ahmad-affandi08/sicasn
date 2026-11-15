<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum TingkatData: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Per instansi',
            self::B => 'Per jabatan (turunan)',
            self::C => 'Per jabatan (resmi)',
        };
    }
}
