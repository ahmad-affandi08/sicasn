<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final class TemplateCsvStatistik
{
    public static function isi(): string
    {
        $contoh = ['2024', 'cpns', 'instansi', 'Nama Instansi', '', '', '', '100', '5000', '', '', '2024-09-17', 'akhir', 'A', 'turunan', 'Nama sumber', 'https://contoh.id/sumber', 'Catatan opsional'];

        return self::keCsv(PenguraiCsvStatistik::KOLOM)."\n".self::keCsv($contoh)."\n";
    }

    public static function keCsv(array $kolom): string
    {
        return implode(',', array_map(fn (string $nilai) => self::lindungi($nilai), $kolom));
    }

    private static function lindungi(string $nilai): string
    {
        $aman = preg_match('/^[=+\-@\t\r]/', $nilai) === 1 ? "'".$nilai : $nilai;

        return preg_match('/[",\n\r]/', $aman) === 1 ? '"'.str_replace('"', '""', $aman).'"' : $aman;
    }
}
