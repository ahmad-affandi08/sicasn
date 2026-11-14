<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class AmbilTautanWhatsapp
{
    public function __construct(private PengaturanWhatsappStore $store) {}

    public function handle(): ?string
    {
        return $this->store->muat()->tautan();
    }
}
