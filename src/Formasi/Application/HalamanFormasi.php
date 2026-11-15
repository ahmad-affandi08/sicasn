<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class HalamanFormasi
{
    public function __construct(
        public array $item,
        public int $total,
    ) {}
}
