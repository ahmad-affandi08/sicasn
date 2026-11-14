<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanSitus;

interface PengaturanSitusStore
{
    public function muat(): PengaturanSitus;

    public function simpan(PengaturanSitus $pengaturan): void;
}
