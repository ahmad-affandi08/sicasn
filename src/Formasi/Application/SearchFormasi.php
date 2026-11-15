<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\NilaiPersaingan;
use Src\Formasi\Domain\PenilaianPersaingan;
use Src\Formasi\Domain\TingkatPersaingan;

final readonly class SearchFormasi
{
    public function __construct(
        private PencarianFormasi $pencarian,
        private PeluangInstansiRepository $peluang,
        private PenyediaAmbangPersaingan $ambang,
        private KebijakanFormasiAktif $kebijakan,
        private KatalogFormasi $katalog,
    ) {}

    public function handle(FilterFormasi $filter): HasilPencarianFormasi
    {
        $ambang = $this->ambang->ambil();
        $peta = $this->peluang->semua();
        $penilaian = array_map(fn (PeluangMentah $p) => $this->nilai($p, $ambang), $peta);
        [$diizinkan, $dikecualikan] = $this->batasInstansi($filter->tingkat, $penilaian);

        $halaman = $this->pencarian->cari($filter, $diizinkan, $dikecualikan);
        $perHalaman = max(1, $filter->perHalaman);
        $terakhirSebenarnya = max(1, (int) ceil($halaman->total / $perHalaman));
        $terakhir = min($terakhirSebenarnya, FilterFormasi::HALAMAN_MAKS);
        $halamanIni = min(max(1, $filter->halaman), $terakhir);

        $item = array_map(fn (RingkasFormasi $f) => new ItemFormasi($f, isset($peta[$f->instansiId]) ? new PeluangFormasi($penilaian[$f->instansiId], $peta[$f->instansiId]) : null), $halaman->item);

        return new HasilPencarianFormasi($item, $halaman->total, $halamanIni, $perHalaman, $terakhir, $this->katalog->hitungAktif($this->kebijakan->aktif()), $ambang, $terakhirSebenarnya > $terakhir);
    }

    private function nilai(PeluangMentah $p, $ambang): PenilaianPersaingan
    {
        return NilaiPersaingan::nilai($p->pelamar, $p->submit, $p->formasi, $p->statusAngka, $ambang);
    }

    private function batasInstansi(?string $kode, array $penilaian): array
    {
        $tingkat = $kode === null || $kode === '' ? null : TingkatPersaingan::tryFrom($kode);

        if ($tingkat === null) {
            return [null, null];
        }

        if ($tingkat === TingkatPersaingan::BelumAdaData) {
            $punyaData = array_keys(array_filter($penilaian, fn (PenilaianPersaingan $p) => $p->tingkat !== TingkatPersaingan::BelumAdaData));

            return [null, $punyaData];
        }

        return [array_keys(array_filter($penilaian, fn (PenilaianPersaingan $p) => $p->tingkat === $tingkat)), null];
    }
}
