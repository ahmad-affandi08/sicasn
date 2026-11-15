<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

interface SumberFormasi
{
    public function item(array $ids): array;
}
