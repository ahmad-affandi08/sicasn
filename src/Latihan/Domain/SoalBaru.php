<?php

declare(strict_types=1);

namespace Src\Latihan\Domain;

use Src\Latihan\Domain\Exception\SoalTidakValid;

final readonly class SoalBaru
{
    private const LABEL_OPSI = ['A', 'B', 'C', 'D', 'E'];

    public function __construct(
        public KategoriSoal $kategori,
        public string $subkategori,
        public string $pertanyaan,
        public string $pembahasan,
        public string $sumberNama,
        public ?string $sumberUrl,
        public string $lisensi,
        public array $opsi,
    ) {
        $this->pastikanKelengkapan();
        $this->pastikanOpsi();
        $this->pastikanSkor();
    }

    public function kode(): string
    {
        return sha1($this->kategori->value.'|'.mb_strtolower(preg_replace('/\s+/u', ' ', trim($this->pertanyaan))));
    }

    private function pastikanKelengkapan(): void
    {
        foreach (['subkategori' => $this->subkategori, 'pertanyaan' => $this->pertanyaan, 'pembahasan' => $this->pembahasan, 'sumber' => $this->sumberNama, 'lisensi' => $this->lisensi] as $nama => $nilai) {
            if (trim($nilai) === '') {
                throw SoalTidakValid::karena("Kolom {$nama} wajib diisi.");
            }
        }

        if ($this->sumberUrl !== null && ! in_array(parse_url($this->sumberUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw SoalTidakValid::karena('URL sumber harus diawali http:// atau https://.');
        }
    }

    private function pastikanOpsi(): void
    {
        $label = array_map(fn (OpsiBaru $opsi) => $opsi->label, $this->opsi);

        if ($label !== self::LABEL_OPSI) {
            throw SoalTidakValid::karena('Opsi harus tepat lima dengan label A sampai E berurutan.');
        }

        $teks = array_map(fn (OpsiBaru $opsi) => trim($opsi->teks), $this->opsi);

        if (in_array('', $teks, true) || count(array_unique($teks)) !== count($teks)) {
            throw SoalTidakValid::karena('Teks opsi wajib diisi dan tidak boleh kembar.');
        }
    }

    private function pastikanSkor(): void
    {
        $skor = array_map(fn (OpsiBaru $opsi) => $opsi->skor, $this->opsi);

        if ($this->kategori->berkunciTunggal()) {
            $sorted = $skor;
            sort($sorted);

            if ($sorted !== [0, 0, 0, 0, KategoriSoal::SKOR_BENAR]) {
                throw SoalTidakValid::karena('Soal TWK dan TIU harus punya tepat satu opsi benar (skor 5) dan sisanya skor 0.');
            }

            return;
        }

        sort($skor);

        if ($skor !== [1, 2, 3, 4, 5]) {
            throw SoalTidakValid::karena('Soal TKP harus memberi skor 1 sampai 5, masing-masing tepat satu kali.');
        }
    }
}
