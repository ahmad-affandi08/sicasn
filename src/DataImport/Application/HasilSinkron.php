<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class HasilSinkron
{
    public function __construct(
        public bool $berhasil,
        public string $pesan,
        public array $ringkasan,
        public ?int $batchId,
    ) {}
}
