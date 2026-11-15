<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final class UrutanJenjang
{
    private const URUTAN = ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'PROFESI', 'S2', 'SPESIALIS', 'S3', 'SUBSPESIALIS', 'LAINNYA'];

    public static function peringkat(string $jenjang): int
    {
        $indeks = array_search($jenjang, self::URUTAN, true);

        return $indeks === false ? count(self::URUTAN) : $indeks;
    }

    public static function urutkan(array $jenjang): array
    {
        usort($jenjang, fn (string $a, string $b) => [self::peringkat($a), $a] <=> [self::peringkat($b), $b]);

        return array_values($jenjang);
    }
}
