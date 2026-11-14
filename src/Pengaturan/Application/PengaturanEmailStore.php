<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanEmail;

interface PengaturanEmailStore
{
    public function muat(): PengaturanEmail;

    public function simpan(PengaturanEmail $pengaturan): void;
}
