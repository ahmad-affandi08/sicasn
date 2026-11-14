<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\NomorWhatsapp;
use Src\Pengaturan\Domain\PengaturanWhatsapp;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanWhatsapp
{
    public function __construct(
        private PengaturanWhatsappStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(string $nomor, string $namaTampilan, string $templatePesan, int $aktorId): void
    {
        $this->store->simpan(new PengaturanWhatsapp(
            trim($nomor) === '' ? null : NomorWhatsapp::dari($nomor),
            trim($namaTampilan),
            trim($templatePesan) === '' ? PengaturanWhatsapp::TEMPLATE_AWAL : trim($templatePesan),
        ));

        $this->audit->catat($aktorId, 'pengaturan.whatsapp_disimpan', null);
    }
}
