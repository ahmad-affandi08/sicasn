<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use DateTimeImmutable;

final readonly class RekamPercobaan
{
    public function __construct(
        public int $id,
        public string $mode,
        public DateTimeImmutable $mulai,
        public DateTimeImmutable $batas,
        public ?DateTimeImmutable $selesai,
    ) {}

    public function sudahSelesai(): bool
    {
        return $this->selesai !== null;
    }

    public function kedaluwarsa(DateTimeImmutable $sekarang): bool
    {
        return $sekarang >= $this->batas;
    }

    public function sisaDetik(DateTimeImmutable $sekarang): int
    {
        return max(0, $this->batas->getTimestamp() - $sekarang->getTimestamp());
    }
}
