<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;
use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NilaiPersaingan;
use Src\Formasi\Domain\StatusAngka;

final readonly class HitungPeluangInstansi
{
    public const TAHUN_ACUAN = 2024;

    public function __construct(private StatistikRepository $statistik) {}

    public function handle(AmbangPersaingan $ambang): array
    {
        $perInstansi = [];

        foreach ($this->statistik->snapshot(self::TAHUN_ACUAN, JenisPengadaan::Cpns, Cakupan::Instansi) as $snapshot) {
            $perInstansi[$snapshot->instansiId][] = $snapshot;
        }

        $daftar = [];

        foreach ($perInstansi as $instansiId => $snapshots) {
            usort($snapshots, fn (SnapshotStatistik $a, SnapshotStatistik $b) => [$b->tanggal, $b->statusAngka === StatusAngka::Akhir ? 1 : 0] <=> [$a->tanggal, $a->statusAngka === StatusAngka::Akhir ? 1 : 0]);

            $terbaru = $snapshots[0];
            [$formasi, $dipinjamDari] = $this->formasiEfektif($snapshots);
            $penilaian = NilaiPersaingan::nilai($terbaru->pelamar, $terbaru->submit, $formasi, $terbaru->statusAngka, $ambang);

            $daftar[] = new InstansiPeluang((int) $instansiId, (string) $terbaru->namaInstansi, $terbaru->tipeInstansi->value, $terbaru, $formasi, $dipinjamDari, $penilaian, $snapshots);
        }

        return $daftar;
    }

    private function formasiEfektif(array $snapshots): array
    {
        foreach ($snapshots as $indeks => $snapshot) {
            if ($snapshot->formasi !== null) {
                return [$snapshot->formasi, $indeks === 0 ? null : $snapshot->tanggal];
            }
        }

        return [null, null];
    }
}
