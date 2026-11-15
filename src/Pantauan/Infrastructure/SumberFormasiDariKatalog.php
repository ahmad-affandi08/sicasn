<?php

declare(strict_types=1);

namespace Src\Pantauan\Infrastructure;

use Src\Formasi\Application\AmbilItemFormasi;
use Src\Pantauan\Application\SumberFormasi;

final readonly class SumberFormasiDariKatalog implements SumberFormasi
{
    public function __construct(private AmbilItemFormasi $ambil) {}

    public function item(array $ids): array
    {
        return $this->ambil->handle($ids);
    }
}
