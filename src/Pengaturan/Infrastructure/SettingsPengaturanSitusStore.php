<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use Src\Pengaturan\Application\PengaturanSitusStore;
use Src\Pengaturan\Application\SettingsRepository;
use Src\Pengaturan\Domain\PengaturanSitus;

final readonly class SettingsPengaturanSitusStore implements PengaturanSitusStore
{
    private const GRUP = 'situs';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): PengaturanSitus
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);

        $url = trim((string) ($nilai['url'] ?? ''));

        return new PengaturanSitus(($nilai['satu_sesi'] ?? '1') === '1', $url === '' ? null : $url);
    }

    public function simpan(PengaturanSitus $pengaturan): void
    {
        $this->settings->simpanBanyak(self::GRUP, ['satu_sesi' => $pengaturan->satuSesi ? '1' : '0', 'url' => $pengaturan->url ?? '']);
    }
}
