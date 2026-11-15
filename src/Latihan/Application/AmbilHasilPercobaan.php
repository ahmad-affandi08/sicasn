<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\PercobaanBelumSelesai;
use Src\Latihan\Domain\Exception\PercobaanTidakDitemukan;
use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\ModeLatihan;

final readonly class AmbilHasilPercobaan
{
    public const STATUS_BENAR = 'benar';

    public const STATUS_SALAH = 'salah';

    public const STATUS_KOSONG = 'kosong';

    public function __construct(
        private PercobaanRepository $percobaan,
        private SelesaikanPercobaan $selesaikan,
        private PenyediaPassingGrade $passingGrade,
    ) {}

    public function handle(int $penggunaId, int $id): HasilPercobaan
    {
        $this->percobaan->cari($penggunaId, $id) ?? throw PercobaanTidakDitemukan::untuk();
        $this->selesaikan->handle($penggunaId, $id, true);

        $rekam = $this->percobaan->cari($penggunaId, $id) ?? throw PercobaanTidakDitemukan::untuk();

        if (! $rekam->sudahSelesai() || $rekam->selesai === null) {
            throw PercobaanBelumSelesai::untuk();
        }

        $soal = $this->soalHasil($this->percobaan->soalDenganKunci($id));
        $kategori = $this->hasilKategori($soal);
        $mode = ModeLatihan::from($rekam->mode);

        return new HasilPercobaan(
            $id,
            $mode->value,
            $mode->label(),
            $rekam->mulai->format(DATE_ATOM),
            $rekam->selesai->format(DATE_ATOM),
            $kategori,
            array_sum(array_map(fn (HasilKategori $butir) => $butir->skor, $kategori)),
            array_sum(array_map(fn (HasilKategori $butir) => $butir->skorMaks, $kategori)),
            $mode === ModeLatihan::Simulasi ? count(array_filter($kategori, fn (HasilKategori $butir) => ! $butir->tercapai)) === 0 : null,
            $soal,
        );
    }

    private function soalHasil(array $baris): array
    {
        return array_map(function (array $b): SoalHasil {
            $dipilih = $b['opsiDipilihId'];
            $skorDipilih = 0;

            foreach ($b['opsi'] as $opsi) {
                if ($opsi['id'] === $dipilih) {
                    $skorDipilih = $opsi['skor'];
                }
            }

            $status = match (true) {
                $dipilih === null => self::STATUS_KOSONG,
                $skorDipilih === KategoriSoal::SKOR_BENAR => self::STATUS_BENAR,
                default => self::STATUS_SALAH,
            };

            return new SoalHasil($b['urutan'], $b['kategori'], $b['subkategori'], $b['pertanyaan'], $b['pembahasan'], $b['sumberNama'], $b['sumberUrl'], $b['lisensi'], $b['opsi'], $dipilih, $b['ragu'], $status);
        }, $baris);
    }

    private function hasilKategori(array $soal): array
    {
        $grade = $this->passingGrade->ambil();
        $hasil = [];

        foreach (KategoriSoal::cases() as $kategori) {
            $butir = array_values(array_filter($soal, fn (SoalHasil $s) => $s->kategori === $kategori->value));

            if ($butir === []) {
                continue;
            }

            $skor = 0;

            foreach ($butir as $s) {
                foreach ($s->opsi as $opsi) {
                    if ($opsi['id'] === $s->opsiDipilihId) {
                        $skor += $opsi['skor'];
                    }
                }
            }

            $hitung = fn (string $status) => count(array_filter($butir, fn (SoalHasil $s) => $s->status === $status));
            $ambang = $grade->setara($kategori, count($butir));

            $hasil[] = new HasilKategori(
                $kategori->value,
                $kategori->label(),
                count($butir),
                $skor,
                count($butir) * KategoriSoal::SKOR_BENAR,
                $hitung(self::STATUS_BENAR),
                $hitung(self::STATUS_SALAH),
                $hitung(self::STATUS_KOSONG),
                $ambang,
                $skor >= $ambang,
            );
        }

        return $hasil;
    }
}
