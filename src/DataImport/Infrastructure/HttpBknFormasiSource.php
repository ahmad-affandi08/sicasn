<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Src\DataImport\Application\BarisBknSumber;
use Src\DataImport\Application\BknFormasiSource;
use Src\DataImport\Domain\Exception\SumberBknGagal;
use Src\Pengaturan\Application\PengaturanEmailStore;
use Throwable;

final readonly class HttpBknFormasiSource implements BknFormasiSource
{
    private const BYTE_KEPALA = 4096;

    public function __construct(private PengaturanEmailStore $email) {}

    public function ambil(string $url): BarisBknSumber
    {
        $jalur = tempnam(sys_get_temp_dir(), 'bkn-');

        try {
            $respons = Http::withHeaders(['User-Agent' => $this->agenPengguna(), 'Accept' => 'application/json'])
                ->connectTimeout(15)
                ->timeout(180)
                ->retry(3, 5000, fn (Throwable $galat) => $galat instanceof ConnectionException || ($galat instanceof RequestException && $galat->response->serverError()), throw: false)
                ->sink($jalur)
                ->get($url);
        } catch (ConnectionException $galat) {
            $this->hapus($jalur);

            throw SumberBknGagal::tidakTersedia('koneksi gagal ('.mb_substr($galat->getMessage(), 0, 120).')');
        }

        if (in_array($respons->status(), [401, 403, 429], true)) {
            $this->hapus($jalur);

            throw SumberBknGagal::diblokir($respons->status());
        }

        if (! $respons->successful()) {
            $this->hapus($jalur);

            throw SumberBknGagal::tidakTersedia("HTTP {$respons->status()}");
        }

        try {
            $this->periksaKepala($jalur);
        } catch (SumberBknGagal $galat) {
            $this->hapus($jalur);

            throw $galat;
        }

        return new BerkasBarisBkn($jalur);
    }

    private function periksaKepala(string $jalur): void
    {
        $kepala = ltrim((string) file_get_contents($jalur, false, null, 0, self::BYTE_KEPALA));

        if (! str_starts_with($kepala, '{')) {
            throw SumberBknGagal::tidakTersedia('respons bukan JSON, kemungkinan halaman tantangan keamanan');
        }

        if (preg_match('/"error"\s*:\s*false/', $kepala) !== 1) {
            throw SumberBknGagal::skemaTidakDikenal('penanda error bernilai true atau tidak ada');
        }

        if (preg_match('/"data"\s*:\s*\[/', $kepala) !== 1) {
            throw SumberBknGagal::skemaTidakDikenal('kolom data bukan daftar');
        }
    }

    private function agenPengguna(): string
    {
        $alamat = $this->email->muat()->pengirim->alamat;

        return $alamat === '' ? 'SICASN/1.0' : "SICASN/1.0 (+mailto:{$alamat})";
    }

    private function hapus(string $jalur): void
    {
        if (is_file($jalur)) {
            unlink($jalur);
        }
    }
}
