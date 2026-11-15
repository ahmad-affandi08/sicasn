<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class ItemRiwayat
{
    public function __construct(
        public int $id,
        public string $mode,
        public string $modeLabel,
        public string $selesai,
        public int $jumlahSoal,
        public array $skor,
        public ?bool $lulus,
    ) {}
}
