<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class ItemFormasi
{
    public function __construct(
        public RingkasFormasi $formasi,
        public ?PeluangFormasi $peluang,
    ) {}
}
