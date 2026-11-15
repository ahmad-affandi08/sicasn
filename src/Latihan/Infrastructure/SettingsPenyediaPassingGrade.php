<?php

declare(strict_types=1);

namespace Src\Latihan\Infrastructure;

use Src\Latihan\Application\PenyediaPassingGrade;
use Src\Latihan\Domain\PassingGrade;
use Src\Pengaturan\Application\SettingsRepository;

final readonly class SettingsPenyediaPassingGrade implements PenyediaPassingGrade
{
    private const GRUP = 'latihan';

    public function __construct(private SettingsRepository $settings) {}

    public function ambil(): PassingGrade
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);
        $awal = PassingGrade::awal();

        return new PassingGrade(
            $this->bilanganPositif($nilai['passing_twk'] ?? null, $awal->twk),
            $this->bilanganPositif($nilai['passing_tiu'] ?? null, $awal->tiu),
            $this->bilanganPositif($nilai['passing_tkp'] ?? null, $awal->tkp),
        );
    }

    private function bilanganPositif(mixed $nilai, int $cadangan): int
    {
        return is_numeric($nilai) && (int) $nilai > 0 ? (int) $nilai : $cadangan;
    }
}
