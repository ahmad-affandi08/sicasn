<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanPembayaran;

interface PengaturanPembayaranStore
{
    public function muat(): PengaturanPembayaran;

    public function simpan(PengaturanPembayaran $pengaturan): void;
}
