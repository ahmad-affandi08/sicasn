<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class HasilPercobaan
{
    public function __construct(
        public int $id,
        public string $mode,
        public string $modeLabel,
        public string $mulai,
        public string $selesai,
        public array $kategori,
        public int $total,
        public int $totalMaks,
        public ?bool $lulus,
        public array $soal,
    ) {}
}
