<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use InvalidArgumentException;
use Src\Formasi\Application\PenyediaAmbangPersaingan;
use Src\Formasi\Domain\AmbangPersaingan;
use Src\Pengaturan\Application\PengaturanPersainganStore;
use Src\Pengaturan\Application\SettingsRepository;

final readonly class SettingsPengaturanPersainganStore implements PengaturanPersainganStore, PenyediaAmbangPersaingan
{
    private const GRUP = 'persaingan';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): AmbangPersaingan
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);

        try {
            return new AmbangPersaingan(
                (float) ($nilai['longgar_maks'] ?? AmbangPersaingan::LONGGAR_MAKS_AWAL),
                (float) ($nilai['ketat_min'] ?? AmbangPersaingan::KETAT_MIN_AWAL),
            );
        } catch (InvalidArgumentException) {
            return AmbangPersaingan::awal();
        }
    }

    public function ambil(): AmbangPersaingan
    {
        return $this->muat();
    }

    public function simpan(AmbangPersaingan $ambang): void
    {
        $this->settings->simpanBanyak(self::GRUP, [
            'longgar_maks' => (string) $ambang->longgarMaks,
            'ketat_min' => (string) $ambang->ketatMin,
        ]);
    }
}
