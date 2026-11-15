<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use DomainException;

final class BatchTidakBisaDibatalkan extends DomainException
{
    public static function tidakDitemukan(): self
    {
        return new self('Batch impor tidak ditemukan.');
    }

    public static function statusTidakSesuai(): self
    {
        return new self('Hanya impor statistik yang berhasil dan belum dibatalkan yang dapat di-rollback.');
    }
}
