<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class PercobaanTidakDitemukan extends DomainException
{
    public static function untuk(): self
    {
        return new self('Percobaan latihan tidak ditemukan.');
    }
}
