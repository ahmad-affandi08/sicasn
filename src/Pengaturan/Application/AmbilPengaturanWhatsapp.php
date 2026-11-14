<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanWhatsapp;

final readonly class AmbilPengaturanWhatsapp
{
    public function __construct(private PengaturanWhatsappStore $store) {}

    public function handle(): PengaturanWhatsapp
    {
        return $this->store->muat();
    }
}
