<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use DomainException;

final class BarisTidakValid extends DomainException
{
    public function __construct(public readonly string $kolom, string $pesan)
    {
        parent::__construct($pesan);
    }
}
