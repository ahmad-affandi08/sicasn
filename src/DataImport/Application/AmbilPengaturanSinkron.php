<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\PengaturanSinkron;

final readonly class AmbilPengaturanSinkron
{
    public function __construct(private PengaturanSinkronStore $store) {}

    public function handle(): PengaturanSinkron
    {
        return $this->store->muat();
    }
}
