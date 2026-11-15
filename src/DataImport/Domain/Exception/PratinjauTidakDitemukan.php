<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use DomainException;

final class PratinjauTidakDitemukan extends DomainException
{
    public static function untuk(string $token): self
    {
        return new self('Pratinjau impor tidak ditemukan atau sudah kedaluwarsa. Unggah ulang berkas CSV.');
    }
}
