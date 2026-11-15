<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\TotalLampiranTidakSesuai;

final class AgregasiLampiran
{
    public function handle(iterable $baris, PolaBarisLampiran $pola, MetadataLampiran $meta): HasilAgregasiLampiran
    {
        $kelompok = [];
        $cocok = 0;
        $tidakCocok = 0;

        foreach ($baris as $teks) {
            $hasil = $pola->cocokkan((string) $teks);

            if ($hasil === null) {
                $tidakCocok++;

                continue;
            }

            [$jabatan, $lokasi] = $hasil;
            $kunci = mb_strtoupper($jabatan).'|'.mb_strtoupper($lokasi);
            $kelompok[$kunci] ??= ['jabatan' => $jabatan, 'lokasi' => $lokasi, 'jumlah' => 0];
            $kelompok[$kunci]['jumlah']++;
            $cocok++;
        }

        if ($meta->totalResmi !== null && $meta->totalResmi !== $cocok) {
            throw TotalLampiranTidakSesuai::untuk($cocok, $meta->totalResmi);
        }

        return new HasilAgregasiLampiran($this->csv(array_values($kelompok), $meta), $cocok, $tidakCocok, count($kelompok));
    }

    private function csv(array $kelompok, MetadataLampiran $meta): string
    {
        usort($kelompok, fn (array $a, array $b) => [$a['jabatan'], $a['lokasi']] <=> [$b['jabatan'], $b['lokasi']]);

        $baris = [TemplateCsvStatistik::keCsv(PenguraiCsvStatistik::KOLOM)];

        foreach ($kelompok as $butir) {
            $baris[] = TemplateCsvStatistik::keCsv([
                (string) $meta->tahun, 'cpns', 'jabatan', $meta->instansi, '', $butir['jabatan'], $butir['lokasi'], '', '', '',
                (string) $butir['jumlah'], $meta->tanggal, $meta->statusAngka, 'B', 'turunan', $meta->sumberNama, (string) $meta->sumberUrl,
                'Dihitung dari jumlah peserta yang dinyatakan lulus seleksi administrasi pada lampiran pengumuman',
            ]);
        }

        return implode("\n", $baris)."\n";
    }
}
