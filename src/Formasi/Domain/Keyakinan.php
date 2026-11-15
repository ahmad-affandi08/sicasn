<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum Keyakinan: string
{
    case Resmi = 'resmi';
    case Turunan = 'turunan';

    public function label(): string
    {
        return match ($this) {
            self::Resmi => 'Sumber resmi',
            self::Turunan => 'Dikutip dari pemberitaan',
        };
    }
}
