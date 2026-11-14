<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PengaturanWhatsapp
{
    public const TEMPLATE_AWAL = 'Halo {nama_admin}, saya ingin berlangganan SICASN.';

    public function __construct(
        public ?NomorWhatsapp $nomor,
        public string $namaTampilan,
        public string $templatePesan,
    ) {}

    public function pesan(): string
    {
        $nama = $this->namaTampilan !== '' ? $this->namaTampilan : 'Admin';

        return str_replace('{nama_admin}', $nama, $this->templatePesan !== '' ? $this->templatePesan : self::TEMPLATE_AWAL);
    }

    public function tautan(): ?string
    {
        return $this->nomor?->tautan($this->pesan());
    }
}
