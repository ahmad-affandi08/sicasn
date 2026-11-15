<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\BankSoalKosong;
use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\ModeLatihan;
use Src\Shared\Application\Clock;

final readonly class MulaiPercobaan
{
    private const PERCOBAAN_DIHINDARI = 3;

    public function __construct(
        private BankSoalRepository $bank,
        private PercobaanRepository $percobaan,
        private SelesaikanPercobaan $selesaikan,
        private Clock $jam,
    ) {}

    public function handle(int $penggunaId, ModeLatihan $mode): int
    {
        $berjalan = $this->percobaan->idBelumSelesai($penggunaId);

        if ($berjalan !== null) {
            $this->selesaikan->handle($penggunaId, $berjalan, true);

            $berjalan = $this->percobaan->idBelumSelesai($penggunaId);

            if ($berjalan !== null) {
                return $berjalan;
            }
        }

        $soalIds = $this->pilihSoal($penggunaId, $mode);
        $sekarang = $this->jam->sekarang();
        $batas = $sekarang->modify('+'.$mode->durasiDetik(count($soalIds)).' seconds');

        return $this->percobaan->buat($penggunaId, $mode->value, $sekarang, $batas, $soalIds, $sekarang);
    }

    private function pilihSoal(int $penggunaId, ModeLatihan $mode): array
    {
        $tersedia = $this->bank->jumlahAktifPerKategori();
        $hindari = $this->percobaan->idSoalTerbaru($penggunaId, self::PERCOBAAN_DIHINDARI);
        $terpilih = [];

        foreach ($mode->komposisi() as $kategoriNilai => $jumlah) {
            $kategori = KategoriSoal::from($kategoriNilai);
            $ambil = min($jumlah, $tersedia[$kategori->value] ?? 0);

            if ($ambil === 0) {
                throw BankSoalKosong::untuk($kategori->label());
            }

            $terpilih = [...$terpilih, ...$this->bank->idAcak($kategori, $ambil, $hindari)];
        }

        return $terpilih;
    }
}
