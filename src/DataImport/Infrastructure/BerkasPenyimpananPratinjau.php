<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Src\DataImport\Application\DataPratinjau;
use Src\DataImport\Application\GalatBaris;
use Src\DataImport\Application\PenyimpananPratinjau;
use Src\Formasi\Application\BarisStatistik;

final class BerkasPenyimpananPratinjau implements PenyimpananPratinjau
{
    private const FOLDER = 'pratinjau-statistik';

    private const MASA_SIMPAN_DETIK = 86400;

    public function simpan(DataPratinjau $data): string
    {
        $this->bersihkanYangLama();

        $token = Str::random(40);

        Storage::disk('local')->put($this->jalur($token), json_encode([
            'nama_berkas' => $data->namaBerkas,
            'total_baris' => $data->totalBaris,
            'baris' => array_map(fn (BarisStatistik $b) => $b->keArray(), $data->baris),
            'galat' => array_map(fn (GalatBaris $g) => ['baris' => $g->baris, 'kolom' => $g->kolom, 'pesan' => $g->pesan, 'nilai' => $g->nilai], $data->galat),
        ], JSON_THROW_ON_ERROR));

        return $token;
    }

    public function ambil(string $token): ?DataPratinjau
    {
        if (! $this->tokenValid($token) || ! Storage::disk('local')->exists($this->jalur($token))) {
            return null;
        }

        $isi = json_decode((string) Storage::disk('local')->get($this->jalur($token)), true);

        if (! is_array($isi)) {
            return null;
        }

        return new DataPratinjau(
            (string) $isi['nama_berkas'],
            array_map(BarisStatistik::dariArray(...), $isi['baris']),
            array_map(fn (array $g) => new GalatBaris($g['baris'], $g['kolom'], $g['pesan'], $g['nilai']), $isi['galat']),
            (int) $isi['total_baris'],
        );
    }

    public function hapus(string $token): void
    {
        if ($this->tokenValid($token)) {
            Storage::disk('local')->delete($this->jalur($token));
        }
    }

    private function tokenValid(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9]{40}$/', $token) === 1;
    }

    private function jalur(string $token): string
    {
        return self::FOLDER.'/'.$token.'.json';
    }

    private function bersihkanYangLama(): void
    {
        foreach (Storage::disk('local')->files(self::FOLDER) as $berkas) {
            if (Storage::disk('local')->lastModified($berkas) < time() - self::MASA_SIMPAN_DETIK) {
                Storage::disk('local')->delete($berkas);
            }
        }
    }
}
