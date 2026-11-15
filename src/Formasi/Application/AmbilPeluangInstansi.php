<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\TingkatPersaingan;

final readonly class AmbilPeluangInstansi
{
    public const TAHUN_ACUAN = 2024;

    public function __construct(
        private StatistikRepository $statistik,
        private PenyediaAmbangPersaingan $ambang,
        private HitungPeluangInstansi $hitung,
        private KatalogFormasi $katalog,
        private KebijakanFormasiAktif $kebijakan,
    ) {}

    public function handle(FilterPeluang $filter): HasilPeluang
    {
        $ambang = $this->ambang->ambil();
        $daftar = $this->hitung->handle($ambang);

        $disaring = array_values(array_filter($daftar, fn (InstansiPeluang $i) => $this->lolos($i, $filter)));
        $urut = in_array($filter->urut, FilterPeluang::URUTAN, true) ? $filter->urut : 'rasio_terendah';
        usort($disaring, fn (InstansiPeluang $a, InstansiPeluang $b) => $this->bandingkan($a, $b, $urut));

        $total = count($disaring);
        $perHalaman = max(1, $filter->perHalaman);
        $terakhir = max(1, (int) ceil($total / $perHalaman));
        $halaman = min(max(1, $filter->halaman), $terakhir);

        $halamanIni = array_slice($disaring, ($halaman - 1) * $perHalaman, $perHalaman);
        $formasiAktif = $this->katalog->hitungAktifPerInstansi($this->kebijakan->aktif(), array_map(fn (InstansiPeluang $i) => $i->instansiId, $halamanIni));

        return new HasilPeluang(
            $halamanIni,
            $total,
            $halaman,
            $perHalaman,
            $terakhir,
            $this->angkaNasional($this->statistik->snapshot(self::TAHUN_ACUAN, JenisPengadaan::Cpns, Cakupan::Nasional)),
            $ambang,
            count($daftar),
            $formasiAktif,
        );
    }

    private function lolos(InstansiPeluang $instansi, FilterPeluang $filter): bool
    {
        if ($filter->cari !== null && $filter->cari !== '' && mb_stripos($instansi->nama, $filter->cari) === false) {
            return false;
        }

        if ($filter->tipe !== null && $filter->tipe !== '' && $instansi->tipe !== $filter->tipe) {
            return false;
        }

        if ($filter->tingkat !== null && $filter->tingkat !== '') {
            $tingkat = TingkatPersaingan::tryFrom($filter->tingkat);

            return $tingkat !== null && $instansi->penilaian->tingkat === $tingkat;
        }

        return true;
    }

    private function bandingkan(InstansiPeluang $a, InstansiPeluang $b, string $urut): int
    {
        $nama = strcasecmp($a->nama, $b->nama);

        return match ($urut) {
            'nama' => $nama,
            'pelamar_tersedikit' => $a->golonganPelamar() <=> $b->golonganPelamar() ?: $this->nilaiNull($a->terbaru->pelamar ?? $a->terbaru->submit) <=> $this->nilaiNull($b->terbaru->pelamar ?? $b->terbaru->submit) ?: $nama,
            'formasi_terbanyak' => ($b->formasi ?? -1) <=> ($a->formasi ?? -1) ?: $nama,
            default => $a->golonganRasio() <=> $b->golonganRasio() ?: $this->nilaiNull($a->penilaian->rasio) <=> $this->nilaiNull($b->penilaian->rasio) ?: $nama,
        };
    }

    private function angkaNasional(array $snapshots): array
    {
        $pilihan = [];
        $bidang = ['pelamar' => 'pelamar', 'submit' => 'submit', 'formasi' => 'formasi', 'lolos_administrasi' => 'lolosAdministrasi'];

        foreach ($bidang as $jenis => $properti) {
            $kandidat = array_values(array_filter($snapshots, fn (SnapshotStatistik $s) => $s->{$properti} !== null));
            usort($kandidat, fn (SnapshotStatistik $a, SnapshotStatistik $b) => [$b->tanggal, $b->keyakinan === Keyakinan::Resmi ? 1 : 0] <=> [$a->tanggal, $a->keyakinan === Keyakinan::Resmi ? 1 : 0]);

            if ($kandidat !== []) {
                $s = $kandidat[0];
                $pilihan[] = new AngkaNasional($jenis, (int) $s->{$properti}, $s->tanggal, $s->sumberNama, $s->sumberUrl, $s->keyakinan);
            }
        }

        return $pilihan;
    }

    private function nilaiNull(int|float|null $nilai): int|float
    {
        return $nilai ?? PHP_INT_MAX;
    }
}
