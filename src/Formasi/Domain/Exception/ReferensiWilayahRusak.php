<?php

declare(strict_types=1);

namespace Src\Formasi\Domain\Exception;

use DomainException;

final class ReferensiWilayahRusak extends DomainException
{
    public static function headerSalah(): self
    {
        return new self('Referensi wilayah rusak: header harus kode,nama,tingkat.');
    }

    public static function barisSalah(int $nomor): self
    {
        return new self("Referensi wilayah rusak pada baris {$nomor}.");
    }
}
