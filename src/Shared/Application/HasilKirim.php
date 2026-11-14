<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class HasilKirim
{
    private function __construct(
        public bool $berhasil,
        public ?string $penyedia,
        public ?string $galat,
        public bool $permanen,
    ) {}

    public static function sukses(string $penyedia): self
    {
        return new self(true, $penyedia, null, false);
    }

    public static function gagal(string $galat, bool $permanen = false): self
    {
        return new self(false, null, $galat, $permanen);
    }
}
