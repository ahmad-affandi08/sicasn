<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\Exception\SoalTidakValid;
use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\OpsiBaru;
use Src\Latihan\Domain\SoalBaru;
use Src\Shared\Application\Clock;

final readonly class MuatBankSoal
{
    public function __construct(
        private BankSoalRepository $bank,
        private Clock $jam,
    ) {}

    public function handle(array $baris): HasilMuatBankSoal
    {
        $soal = [];

        foreach ($baris as $indeks => $butir) {
            try {
                $soal[] = $this->petakan($butir);
            } catch (SoalTidakValid $galat) {
                throw SoalTidakValid::karena('Soal ke-'.($indeks + 1).': '.$galat->getMessage());
            }
        }

        $kode = array_map(fn (SoalBaru $butir) => $butir->kode(), $soal);

        if (count($kode) !== count(array_unique($kode))) {
            throw SoalTidakValid::karena('Ada pertanyaan kembar dalam berkas soal.');
        }

        return $this->bank->muat($soal, $this->jam->sekarang());
    }

    private function petakan(array $baris): SoalBaru
    {
        return new SoalBaru(
            KategoriSoal::tryFrom((string) ($baris['kategori'] ?? '')) ?? throw SoalTidakValid::karena('Kategori harus twk, tiu, atau tkp.'),
            (string) ($baris['subkategori'] ?? ''),
            (string) ($baris['pertanyaan'] ?? ''),
            (string) ($baris['pembahasan'] ?? ''),
            (string) ($baris['sumber_nama'] ?? ''),
            isset($baris['sumber_url']) && $baris['sumber_url'] !== '' ? (string) $baris['sumber_url'] : null,
            (string) ($baris['lisensi'] ?? ''),
            array_map(fn (array $opsi) => new OpsiBaru((string) ($opsi['label'] ?? ''), (string) ($opsi['teks'] ?? ''), (int) ($opsi['skor'] ?? -1)), $baris['opsi'] ?? []),
        );
    }
}
