<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final class PetaPratinjau
{
    private const MAKS_GALAT_DITAMPILKAN = 50;

    public static function dari(?string $token, DataPratinjau $data, ?string $galatBerkas = null): PratinjauImpor
    {
        $ringkasan = [];

        foreach ($data->baris as $baris) {
            $kunci = $baris->sumberNama.'|'.$baris->tanggalSnapshot.'|'.$baris->statusAngka->value.'|'.$baris->cakupan->value;
            $ringkasan[$kunci] ??= ['sumber' => $baris->sumberNama, 'tanggal' => $baris->tanggalSnapshot, 'status' => $baris->statusAngka->value, 'cakupan' => $baris->cakupan->value, 'jumlah' => 0];
            $ringkasan[$kunci]['jumlah']++;
        }

        return new PratinjauImpor(
            $token,
            $data->namaBerkas,
            $data->totalBaris,
            count($data->baris),
            count($data->galat),
            array_map(fn (GalatBaris $g) => ['baris' => $g->baris, 'kolom' => $g->kolom, 'pesan' => $g->pesan, 'nilai' => $g->nilai], array_slice($data->galat, 0, self::MAKS_GALAT_DITAMPILKAN)),
            array_values($ringkasan),
            $galatBerkas,
        );
    }
}
