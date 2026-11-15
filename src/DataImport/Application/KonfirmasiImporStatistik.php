<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\PratinjauTidakDitemukan;
use Src\DataImport\Domain\StatusBatch;
use Src\Formasi\Application\ReferensiInstansi;
use Src\Formasi\Application\StatistikRepository;
use Src\Formasi\Domain\Cakupan;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class KonfirmasiImporStatistik
{
    private const TIPE = 'csv_statistik';

    public function __construct(
        private PenyimpananPratinjau $penyimpanan,
        private ImportBatchRepository $batch,
        private ReferensiInstansi $instansi,
        private StatistikRepository $statistik,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(string $token, ?int $aktorId): HasilImporStatistik
    {
        $data = $this->penyimpanan->ambil($token) ?? throw PratinjauTidakDitemukan::untuk($token);
        $sekarang = $this->clock->sekarang();
        $batchId = $this->batch->mulai(self::TIPE, $data->namaBerkas, $aktorId, $sekarang);

        $nama = [];

        foreach ($data->baris as $baris) {
            if ($baris->cakupan === Cakupan::Instansi) {
                $nama[$baris->namaInstansi] = $baris->namaInstansi;
            }
        }

        $hasil = $this->statistik->simpanBanyak($data->baris, $this->instansi->pastikan(array_values($nama)), $batchId, $sekarang);
        $jumlahGalat = count($data->galat);

        $this->batch->catatGalat($batchId, $data->galat);
        $this->batch->selesai($batchId, $jumlahGalat > 0 ? StatusBatch::Sebagian : StatusBatch::Sukses, [
            'berkas' => $data->namaBerkas,
            'total_baris' => $data->totalBaris,
            'baris_baru' => $hasil->baru,
            'baris_duplikat' => $hasil->duplikat,
            'baris_gagal' => $jumlahGalat,
        ], null, $this->clock->sekarang());

        $this->audit->catat($aktorId, 'statistik.diimpor', null, ['batch' => $batchId, 'berkas' => $data->namaBerkas, 'baru' => $hasil->baru]);
        $this->penyimpanan->hapus($token);

        return new HasilImporStatistik($batchId, $hasil->baru, $hasil->duplikat, $jumlahGalat);
    }
}
