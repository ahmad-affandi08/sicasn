<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum StatusAngka: string
{
    case Sementara = 'sementara';
    case Akhir = 'akhir';

    public function label(): string
    {
        return match ($this) {
            self::Sementara => 'Data sementara',
            self::Akhir => 'Data akhir pendaftaran',
        };
    }
}
