<?php

declare(strict_types=1);

namespace Src\DataImport\Domain;

enum FrekuensiSinkron: string
{
    case Harian = 'harian';
    case EnamJam = 'enam_jam';

    public function label(): string
    {
        return match ($this) {
            self::Harian => 'Sekali sehari (02:00 WIB)',
            self::EnamJam => 'Setiap 6 jam (selama masa pendaftaran)',
        };
    }
}
