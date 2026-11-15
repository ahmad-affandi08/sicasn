<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\JawabanTidakValid;
use Src\Latihan\Domain\Exception\PercobaanSudahSelesai;
use Src\Latihan\Domain\Exception\PercobaanTidakDitemukan;
use Src\Shared\Application\Clock;

final readonly class SimpanJawaban
{
    private const TOLERANSI_DETIK = 5;

    public function __construct(
        private PercobaanRepository $percobaan,
        private Clock $jam,
    ) {}

    public function handle(int $penggunaId, int $percobaanId, int $soalId, ?int $opsiId, bool $ragu): int
    {
        $rekam = $this->percobaan->cari($penggunaId, $percobaanId) ?? throw PercobaanTidakDitemukan::untuk();
        $sekarang = $this->jam->sekarang();

        if ($rekam->sudahSelesai() || $sekarang->getTimestamp() > $rekam->batas->getTimestamp() + self::TOLERANSI_DETIK) {
            throw PercobaanSudahSelesai::untuk();
        }

        if ($opsiId !== null && ! $this->percobaan->opsiMilikSoal($soalId, $opsiId)) {
            throw JawabanTidakValid::untuk();
        }

        if (! $this->percobaan->simpanJawaban($percobaanId, $soalId, $opsiId, $ragu)) {
            throw JawabanTidakValid::untuk();
        }

        return $rekam->sisaDetik($sekarang);
    }
}
