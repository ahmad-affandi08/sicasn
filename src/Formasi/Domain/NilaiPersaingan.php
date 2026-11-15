<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final class NilaiPersaingan
{
    public static function nilai(?int $pelamar, ?int $submit, ?int $formasi, StatusAngka $status, AmbangPersaingan $ambang, ?int $lolosAdministrasi = null): PenilaianPersaingan
    {
        $dasar = $pelamar ?? $submit ?? $lolosAdministrasi;

        if ($formasi === null || $formasi <= 0 || $dasar === null) {
            return new PenilaianPersaingan(TingkatPersaingan::BelumAdaData, null, false);
        }

        $rasio = $dasar / $formasi;
        $batasBawah = $pelamar === null || $status === StatusAngka::Sementara;

        $tingkat = match (true) {
            $rasio < $ambang->longgarMaks => TingkatPersaingan::Longgar,
            $rasio <= $ambang->ketatMin => TingkatPersaingan::Sedang,
            default => TingkatPersaingan::Ketat,
        };

        if ($batasBawah && $tingkat === TingkatPersaingan::Longgar) {
            return new PenilaianPersaingan(TingkatPersaingan::BelumFinal, $rasio, true);
        }

        return new PenilaianPersaingan($tingkat, $rasio, $batasBawah);
    }
}
