<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Shared\Application\EmailTerkonfigurasi;

final readonly class AmbilKesiapan
{
    public function __construct(
        private EmailTerkonfigurasi $email,
        private PengaturanWhatsappStore $whatsapp,
        private PengaturanPembayaranStore $pembayaran,
    ) {}

    public function handle(): Kesiapan
    {
        return new Kesiapan(
            $this->email->siap(),
            $this->whatsapp->muat()->nomor !== null,
            $this->pembayaran->muat()->terisi(),
        );
    }
}
