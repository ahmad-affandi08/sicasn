<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NilaiPersaingan;

final readonly class PerkayaFormasi
{
    public function __construct(
        private PeluangInstansiRepository $peluang,
        private PenyediaAmbangPersaingan $ambang,
        private StatistikRepository $statistik,
    ) {}

    public function handle(array $ringkas): array
    {
        if ($ringkas === []) {
            return [];
        }

        $ambang = $this->ambang->ambil();
        $peta = $this->peluang->semua();
        $perJabatan = $this->statistik->jabatan(HitungPeluangInstansi::TAHUN_ACUAN, JenisPengadaan::Cpns, array_map(fn (RingkasFormasi $f) => [$f->instansiId, $f->jabatanNormal], $ringkas));

        return array_map(fn (RingkasFormasi $f) => new ItemFormasi(
            $f,
            isset($peta[$f->instansiId]) ? $this->peluangInstansi($peta[$f->instansiId], $ambang) : null,
            $this->peluangJabatan($f, $perJabatan, $ambang),
        ), $ringkas);
    }

    private function peluangInstansi(PeluangMentah $mentah, AmbangPersaingan $ambang): PeluangFormasi
    {
        return new PeluangFormasi(NilaiPersaingan::nilai($mentah->pelamar, $mentah->submit, $mentah->formasi, $mentah->statusAngka, $ambang), $mentah);
    }

    private function peluangJabatan(RingkasFormasi $formasi, array $perJabatan, AmbangPersaingan $ambang): ?PeluangJabatan
    {
        $snapshot = PencocokPeluangJabatan::pilih($formasi, $perJabatan[$formasi->instansiId.'|'.$formasi->jabatanNormal] ?? []);

        if ($snapshot === null) {
            return null;
        }

        return new PeluangJabatan(NilaiPersaingan::nilai($snapshot->pelamar, $snapshot->submit, $snapshot->formasi, $snapshot->statusAngka, $ambang, $snapshot->lolosAdministrasi), $snapshot);
    }
}
