<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use Src\Formasi\Application\ItemFormasi;

final readonly class ItemPantauan
{
    public function __construct(
        public ItemFormasi $item,
        public ?string $catatan,
        public string $ditambahkanAt,
    ) {}
}
