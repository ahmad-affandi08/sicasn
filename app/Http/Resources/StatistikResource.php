<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\DataImport\Application\PratinjauImpor;
use Src\DataImport\Application\RingkasBatch;

final class StatistikResource
{
    public static function riwayat(RingkasBatch $batch): array
    {
        return [
            'id' => $batch->id,
            'status' => $batch->status->value,
            'mulaiAt' => $batch->mulaiAt,
            'selesaiAt' => $batch->selesaiAt,
            'berkas' => $batch->sumber,
            'ringkasan' => $batch->ringkasan,
            'bisaDibatalkan' => $batch->status->berhasil(),
        ];
    }

    public static function pratinjau(PratinjauImpor $pratinjau): array
    {
        return [
            'token' => $pratinjau->token,
            'namaBerkas' => $pratinjau->namaBerkas,
            'totalBaris' => $pratinjau->totalBaris,
            'barisValid' => $pratinjau->barisValid,
            'jumlahGalat' => $pratinjau->jumlahGalat,
            'galat' => $pratinjau->galat,
            'ringkasanSumber' => $pratinjau->ringkasanSumber,
            'galatBerkas' => $pratinjau->galatBerkas,
        ];
    }
}
