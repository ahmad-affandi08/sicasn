<?php

declare(strict_types=1);

namespace Src\Identity\Domain\Exception;

use DomainException;

final class PenggunaTidakDitemukan extends DomainException
{
    public static function untuk(int $id): self
    {
        return new self("Pengguna {$id} tidak ditemukan.");
    }
}
