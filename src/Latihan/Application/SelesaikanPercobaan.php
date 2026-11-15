<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\PercobaanTidakDitemukan;
use Src\Shared\Application\Clock;

final readonly class SelesaikanPercobaan
{
    public function __construct(
        private PercobaanRepository $percobaan,
        private Clock $jam,
    ) {}

    public function handle(int $penggunaId, int $id, bool $hanyaBilaKedaluwarsa = false): void
    {
        $rekam = $this->percobaan->cari($penggunaId, $id) ?? throw PercobaanTidakDitemukan::untuk();
        $sekarang = $this->jam->sekarang();

        if ($rekam->sudahSelesai() || ($hanyaBilaKedaluwarsa && ! $rekam->kedaluwarsa($sekarang))) {
            return;
        }

        $this->percobaan->tandaiSelesai($id, $sekarang, $this->percobaan->ringkasSkor($id));
    }
}
