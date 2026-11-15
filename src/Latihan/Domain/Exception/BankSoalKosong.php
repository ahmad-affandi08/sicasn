<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class BankSoalKosong extends DomainException
{
    public static function untuk(string $nama): self
    {
        return new self("Bank soal {$nama} belum tersedia.");
    }
}
