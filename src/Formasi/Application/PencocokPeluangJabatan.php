<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

final class PencocokPeluangJabatan
{
    public static function pilih(RingkasFormasi $formasi, array $snapshot): ?SnapshotJabatan
    {
        $cocok = array_values(array_filter($snapshot, fn (SnapshotJabatan $s) => self::lokasiCocok($formasi, $s)));

        if ($cocok === []) {
            return null;
        }

        usort($cocok, fn (SnapshotJabatan $a, SnapshotJabatan $b) => self::peringkat($b) <=> self::peringkat($a));

        return $cocok[0];
    }

    public static function semuaYangCocok(RingkasFormasi $formasi, array $snapshot): array
    {
        $cocok = array_values(array_filter($snapshot, fn (SnapshotJabatan $s) => self::lokasiCocok($formasi, $s)));
        usort($cocok, fn (SnapshotJabatan $a, SnapshotJabatan $b) => self::peringkat($b) <=> self::peringkat($a));

        return $cocok;
    }

    private static function lokasiCocok(RingkasFormasi $formasi, SnapshotJabatan $snapshot): bool
    {
        if ($snapshot->wilayahId !== null) {
            return $formasi->wilayahId === $snapshot->wilayahId;
        }

        if ($snapshot->lokasi !== null) {
            return NamaNormal::dari($snapshot->lokasi) === NamaNormal::dari($formasi->lokasi);
        }

        return true;
    }

    private static function peringkat(SnapshotJabatan $s): array
    {
        return [
            $s->lokasi !== null ? 1 : 0,
            $s->tanggal,
            match ($s->tingkatData) {
                TingkatData::C => 3,
                TingkatData::B => 2,
                TingkatData::A => 1,
            },
            $s->statusAngka === StatusAngka::Akhir ? 1 : 0,
            $s->keyakinan === Keyakinan::Resmi ? 1 : 0,
        ];
    }
}
