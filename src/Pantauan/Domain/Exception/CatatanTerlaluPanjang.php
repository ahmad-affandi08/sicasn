<?php

declare(strict_types=1);

namespace Src\Pantauan\Domain\Exception;

use DomainException;
use Src\Pantauan\Domain\BatasPantauan;

final class CatatanTerlaluPanjang extends DomainException
{
    public static function untuk(): self
    {
        return new self('Catatan maksimal '.BatasPantauan::MAKS_CATATAN.' karakter.');
    }
}
