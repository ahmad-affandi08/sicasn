<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\DataImport\Application\EventTercatat;
use Src\DataImport\Application\EventTerdeteksiRepository;

final class EloquentEventTerdeteksiRepository implements EventTerdeteksiRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function catat(array $event, DateTimeImmutable $sekarang): int
    {
        $waktu = $sekarang->format(self::FORMAT);
        $baru = 0;

        foreach ($event as $butir) {
            $sudahAda = DB::table('event_terdeteksis')->where('event_id', $butir->eventId)->exists();

            $nilai = [
                'nama_event' => mb_substr($butir->namaEvent, 0, 255),
                'jenis_pengadaan_teramati' => mb_substr(implode(', ', $butir->jenisPengadaan), 0, 255),
                'jumlah_baris' => $butir->jumlahBaris,
                'diabaikan' => $butir->diabaikan,
                'last_seen_at' => $waktu,
                'updated_at' => $waktu,
            ];

            if ($sudahAda) {
                DB::table('event_terdeteksis')->where('event_id', $butir->eventId)->update($nilai);

                continue;
            }

            DB::table('event_terdeteksis')->insert($nilai + ['event_id' => $butir->eventId, 'first_seen_at' => $waktu, 'created_at' => $waktu]);
            $baru += $butir->diabaikan ? 0 : 1;
        }

        return $baru;
    }

    public function terbaru(int $jumlah): array
    {
        return DB::table('event_terdeteksis')->orderByDesc('last_seen_at')->orderByDesc('id')->limit($jumlah)->get()->map(fn ($e) => $this->petakan($e))->all();
    }

    public function belumDiberitahukan(): array
    {
        return DB::table('event_terdeteksis')->where('diabaikan', false)->whereNull('diberitahukan_at')->orderBy('id')->get()->map(fn ($e) => $this->petakan($e))->all();
    }

    public function tandaiDiberitahukan(DateTimeImmutable $sekarang): void
    {
        DB::table('event_terdeteksis')->where('diabaikan', false)->whereNull('diberitahukan_at')->update(['diberitahukan_at' => $sekarang->format(self::FORMAT)]);
    }

    private function petakan(object $baris): EventTercatat
    {
        return new EventTercatat(
            $baris->event_id,
            $baris->nama_event,
            $baris->jenis_pengadaan_teramati,
            (int) $baris->jumlah_baris,
            (bool) $baris->diabaikan,
            $baris->first_seen_at,
            $baris->diberitahukan_at !== null,
        );
    }
}
