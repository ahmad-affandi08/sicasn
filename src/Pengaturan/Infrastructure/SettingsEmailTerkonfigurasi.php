<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use Src\Pengaturan\Application\PengaturanEmailStore;
use Src\Shared\Application\EmailTerkonfigurasi;

final readonly class SettingsEmailTerkonfigurasi implements EmailTerkonfigurasi
{
    public function __construct(private PengaturanEmailStore $store) {}

    public function siap(): bool
    {
        return $this->store->muat()->terkonfigurasi();
    }
}
