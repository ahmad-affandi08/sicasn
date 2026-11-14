<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use Src\Pengaturan\Application\PengaturanWhatsappStore;
use Src\Pengaturan\Application\SettingsRepository;
use Src\Pengaturan\Domain\Exception\NomorWhatsappTidakValid;
use Src\Pengaturan\Domain\NomorWhatsapp;
use Src\Pengaturan\Domain\PengaturanWhatsapp;

final readonly class SettingsPengaturanWhatsappStore implements PengaturanWhatsappStore
{
    private const GRUP = 'whatsapp';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): PengaturanWhatsapp
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);

        return new PengaturanWhatsapp(
            $this->nomor((string) ($nilai['nomor'] ?? '')),
            (string) ($nilai['nama_tampilan'] ?? ''),
            (string) ($nilai['template_pesan'] ?? PengaturanWhatsapp::TEMPLATE_AWAL),
        );
    }

    public function simpan(PengaturanWhatsapp $pengaturan): void
    {
        $this->settings->simpanBanyak(self::GRUP, [
            'nomor' => $pengaturan->nomor?->digit,
            'nama_tampilan' => $pengaturan->namaTampilan,
            'template_pesan' => $pengaturan->templatePesan,
        ]);
    }

    private function nomor(string $nilai): ?NomorWhatsapp
    {
        if ($nilai === '') {
            return null;
        }

        try {
            return NomorWhatsapp::dari($nilai);
        } catch (NomorWhatsappTidakValid) {
            return null;
        }
    }
}
