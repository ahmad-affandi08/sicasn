<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanPembayaran;

final readonly class AmbilPengaturanPembayaran
{
    public function __construct(private PengaturanPembayaranStore $store) {}

    public function handle(): PengaturanPembayaran
    {
        return $this->store->muat();
    }
}
