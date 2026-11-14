<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

enum Enkripsi: string
{
    case Ssl = 'ssl';
    case Tls = 'tls';
    case Tanpa = 'tanpa';

    public function skema(): string
    {
        return $this === self::Ssl ? 'smtps' : 'smtp';
    }

    public function label(): string
    {
        return match ($this) {
            self::Ssl => 'SSL/TLS (biasanya port 465)',
            self::Tls => 'STARTTLS (biasanya port 587)',
            self::Tanpa => 'Tanpa enkripsi',
        };
    }
}
