<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure;

use Src\DataImport\Application\PengaturanSinkronStore;
use Src\DataImport\Domain\FrekuensiSinkron;
use Src\DataImport\Domain\PengaturanSinkron;
use Src\Pengaturan\Application\SettingsRepository;

final readonly class SettingsPengaturanSinkronStore implements PengaturanSinkronStore
{
    private const GRUP = 'sinkron';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): PengaturanSinkron
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);

        return new PengaturanSinkron(
            (string) ($nilai['url'] ?? PengaturanSinkron::URL_AWAL) ?: PengaturanSinkron::URL_AWAL,
            FrekuensiSinkron::tryFrom((string) ($nilai['frekuensi'] ?? '')) ?? FrekuensiSinkron::Harian,
        );
    }

    public function simpan(PengaturanSinkron $pengaturan): void
    {
        $this->settings->simpanBanyak(self::GRUP, ['url' => $pengaturan->url, 'frekuensi' => $pengaturan->frekuensi->value]);
    }
}
