<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\Formasi\Application\KatalogFormasi;

final readonly class AmbilStatusSinkron
{
    public function __construct(
        private ImportBatchRepository $batch,
        private EventTerdeteksiRepository $event,
        private PengaturanSinkronStore $pengaturan,
        private KebijakanJenisPengadaan $kebijakan,
        private KatalogFormasi $katalog,
    ) {}

    public function handle(): StatusSinkron
    {
        $riwayat = $this->batch->terbaru('sinkron_bkn', 10);
        $aktif = $this->kebijakan->aktif();

        return new StatusSinkron(
            $riwayat,
            $this->batch->terakhirBerhasil('sinkron_bkn'),
            $this->batch->adaYangBerjalan('sinkron_bkn'),
            $this->event->terbaru(20),
            $this->pengaturan->muat(),
            array_map(fn ($jenis) => $jenis->label(), $aktif),
            $this->katalog->hitungAktif($aktif),
            $this->katalog->hitungLokasiTidakTerpetakan($aktif),
        );
    }
}
