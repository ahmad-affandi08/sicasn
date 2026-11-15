<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\PengaturanSinkron;

interface PengaturanSinkronStore
{
    public function muat(): PengaturanSinkron;

    public function simpan(PengaturanSinkron $pengaturan): void;
}
