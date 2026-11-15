<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\PercobaanTidakDitemukan;
use Src\Shared\Application\Clock;

final readonly class AmbilPercobaan
{
    public function __construct(
        private PercobaanRepository $percobaan,
        private SelesaikanPercobaan $selesaikan,
        private Clock $jam,
    ) {}

    public function handle(int $penggunaId, int $id): PercobaanBerjalan
    {
        $rekam = $this->percobaan->cari($penggunaId, $id) ?? throw PercobaanTidakDitemukan::untuk();
        $sekarang = $this->jam->sekarang();

        if (! $rekam->sudahSelesai() && $rekam->kedaluwarsa($sekarang)) {
            $this->selesaikan->handle($penggunaId, $id, true);

            return new PercobaanBerjalan($id, $rekam->mode, 0, $this->durasi($rekam), true, []);
        }

        if ($rekam->sudahSelesai()) {
            return new PercobaanBerjalan($id, $rekam->mode, 0, $this->durasi($rekam), true, []);
        }

        return new PercobaanBerjalan(
            $id,
            $rekam->mode,
            $rekam->sisaDetik($sekarang),
            $this->durasi($rekam),
            false,
            $this->percobaan->soalUntukDikerjakan($id),
        );
    }

    private function durasi(RekamPercobaan $rekam): int
    {
        return $rekam->batas->getTimestamp() - $rekam->mulai->getTimestamp();
    }
}
