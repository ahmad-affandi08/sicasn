<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class SoalTidakValid extends DomainException
{
    public static function karena(string $alasan): self
    {
        return new self($alasan);
    }
}
