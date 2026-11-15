<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NilaiPersaingan;

final readonly class AmbilDetailFormasi
{
    public const TAUTAN_RESMI = 'https://sscasn.bkn.go.id';

    public function __construct(
        private DetailFormasiRepository $repository,
        private PeluangInstansiRepository $peluang,
        private StatistikRepository $statistik,
        private PenyediaAmbangPersaingan $ambang,
    ) {}

    public function handle(int $id): ?DetailFormasi
    {
        $rincian = $this->repository->cari($id);

        if ($rincian === null) {
            return null;
        }

        $instansiId = $rincian->ringkas->instansiId;
        $mentah = $this->peluang->semua()[$instansiId] ?? null;
        $ambang = $this->ambang->ambil();
        $peluang = $mentah === null ? null : new PeluangFormasi(NilaiPersaingan::nilai($mentah->pelamar, $mentah->submit, $mentah->formasi, $mentah->statusAngka, $ambang), $mentah);
        $riwayat = $this->statistik->snapshot(HitungPeluangInstansi::TAHUN_ACUAN, JenisPengadaan::Cpns, Cakupan::Instansi, $instansiId);

        $kunci = $instansiId.'|'.$rincian->ringkas->jabatanNormal;
        $perJabatan = $this->statistik->jabatan(HitungPeluangInstansi::TAHUN_ACUAN, JenisPengadaan::Cpns, [[$instansiId, $rincian->ringkas->jabatanNormal]])[$kunci] ?? [];
        $cocok = PencocokPeluangJabatan::semuaYangCocok($rincian->ringkas, $perJabatan);
        $terpilih = $cocok[0] ?? null;
        $peluangJabatan = $terpilih === null ? null : new PeluangJabatan(NilaiPersaingan::nilai($terpilih->pelamar, $terpilih->submit, $terpilih->formasi, $terpilih->statusAngka, $ambang, $terpilih->lolosAdministrasi), $terpilih);

        return new DetailFormasi($rincian, $peluang, $riwayat, self::TAUTAN_RESMI, $ambang, $peluangJabatan, $cocok);
    }
}
