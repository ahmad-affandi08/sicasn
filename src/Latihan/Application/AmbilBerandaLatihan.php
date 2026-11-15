<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\ModeLatihan;
use Src\Latihan\Domain\PassingGrade;

final readonly class AmbilBerandaLatihan
{
    private const BATAS_RIWAYAT = 20;

    public function __construct(
        private BankSoalRepository $bank,
        private PercobaanRepository $percobaan,
        private PenyediaPassingGrade $passingGrade,
    ) {}

    public function handle(int $penggunaId): BerandaLatihan
    {
        $grade = $this->passingGrade->ambil();
        $ambang = [];

        foreach (KategoriSoal::cases() as $kategori) {
            $ambang[$kategori->value] = ['resmi' => $grade->resmi($kategori), 'maks' => $kategori->skorMaksResmi(), 'jumlah' => $kategori->jumlahResmi()];
        }

        return new BerandaLatihan(
            $this->bank->jumlahAktifPerKategori(),
            $this->percobaan->idBelumSelesai($penggunaId),
            array_map(fn (array $b) => $this->itemRiwayat($b, $grade), $this->percobaan->riwayat($penggunaId, self::BATAS_RIWAYAT)),
            $ambang,
        );
    }

    private function itemRiwayat(array $baris, PassingGrade $grade): ItemRiwayat
    {
        $mode = ModeLatihan::from($baris['mode']);
        $skor = [];
        $lulus = true;

        foreach (KategoriSoal::cases() as $kategori) {
            $jumlah = $baris['jumlah'][$kategori->value];

            if ($jumlah === 0) {
                continue;
            }

            $skor[$kategori->value] = $baris['skor'][$kategori->value];
            $lulus = $lulus && $baris['skor'][$kategori->value] >= $grade->setara($kategori, $jumlah);
        }

        return new ItemRiwayat($baris['id'], $mode->value, $mode->label(), $baris['selesai'], array_sum($baris['jumlah']), $skor, $mode === ModeLatihan::Simulasi ? $lulus : null);
    }
}
