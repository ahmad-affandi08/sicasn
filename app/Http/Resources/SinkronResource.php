<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\DataImport\Application\EventTercatat;
use Src\DataImport\Application\RingkasBatch;
use Src\DataImport\Application\StatusSinkron;
use Src\DataImport\Domain\FrekuensiSinkron;
use Src\Formasi\Domain\JenisPengadaan;

final class SinkronResource
{
    public static function dari(StatusSinkron $status): array
    {
        return [
            'sedangBerjalan' => $status->sedangBerjalan,
            'formasiAktif' => $status->formasiAktif,
            'lokasiTidakTerpetakan' => $status->lokasiTidakTerpetakan,
            'jenisAktif' => $status->jenisAktif,
            'terakhirBerhasil' => $status->terakhirBerhasil === null ? null : self::batch($status->terakhirBerhasil),
            'riwayat' => array_map(self::batch(...), $status->riwayat),
            'event' => array_map(self::event(...), $status->event),
            'pengaturan' => ['url' => $status->pengaturan->url, 'frekuensi' => $status->pengaturan->frekuensi->value],
            'frekuensi' => array_map(fn (FrekuensiSinkron $f) => ['kode' => $f->value, 'label' => $f->label()], FrekuensiSinkron::cases()),
        ];
    }

    public static function batch(RingkasBatch $batch): array
    {
        return [
            'id' => $batch->id,
            'status' => $batch->status->value,
            'mulaiAt' => $batch->mulaiAt,
            'selesaiAt' => $batch->selesaiAt,
            'ringkasan' => $batch->ringkasan,
        ];
    }

    public static function event(EventTercatat $event): array
    {
        return [
            'eventId' => $event->eventId,
            'nama' => $event->namaEvent,
            'jenis' => implode(', ', array_map(fn (string $kode) => JenisPengadaan::tryFrom(trim($kode))?->label() ?? trim($kode), explode(',', $event->jenisPengadaan))),
            'jumlahBaris' => $event->jumlahBaris,
            'diabaikan' => $event->diabaikan,
            'pertamaKaliTerlihat' => $event->pertamaKaliTerlihat,
            'sudahDiberitahukan' => $event->sudahDiberitahukan,
        ];
    }
}
