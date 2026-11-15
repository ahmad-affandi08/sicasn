<?php

declare(strict_types=1);

namespace Src\Pantauan\Domain\Exception;

use DomainException;

final class FormasiTidakDitemukan extends DomainException
{
    public static function untuk(): self
    {
        return new self('Formasi tidak ditemukan atau sudah dihapus dari katalog.');
    }
}
