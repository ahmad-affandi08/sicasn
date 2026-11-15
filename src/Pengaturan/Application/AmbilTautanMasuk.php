<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class AmbilTautanMasuk
{
    public function __construct(private PengaturanSitusStore $store) {}

    public function handle(string $cadangan): string
    {
        return $this->store->muat()->tautanMasuk() ?? $cadangan;
    }
}
