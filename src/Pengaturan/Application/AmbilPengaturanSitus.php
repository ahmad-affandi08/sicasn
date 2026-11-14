<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanSitus;

final readonly class AmbilPengaturanSitus
{
    public function __construct(private PengaturanSitusStore $store) {}

    public function handle(): PengaturanSitus
    {
        return $this->store->muat();
    }
}
