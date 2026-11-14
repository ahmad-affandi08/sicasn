<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanWhatsapp;

interface PengaturanWhatsappStore
{
    public function muat(): PengaturanWhatsapp;

    public function simpan(PengaturanWhatsapp $pengaturan): void;
}
