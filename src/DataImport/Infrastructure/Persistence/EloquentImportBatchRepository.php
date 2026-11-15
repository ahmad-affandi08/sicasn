<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\DataImport\Application\GalatBaris;
use Src\DataImport\Application\ImportBatchRepository;
use Src\DataImport\Application\RingkasBatch;
use Src\DataImport\Domain\StatusBatch;

final class EloquentImportBatchRepository implements ImportBatchRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function mulai(string $tipe, ?string $sumber, ?int $dibuatOleh, DateTimeImmutable $sekarang): int
    {
        return (int) DB::table('import_batches')->insertGetId([
            'tipe' => $tipe,
            'status' => StatusBatch::Berjalan->value,
            'mulai_at' => $sekarang->format(self::FORMAT),
            'sumber' => $sumber === null ? null : mb_substr($sumber, 0, 255),
            'dibuat_oleh' => $dibuatOleh,
            'created_at' => $sekarang->format(self::FORMAT),
            'updated_at' => $sekarang->format(self::FORMAT),
        ]);
    }

    public function tutupYangKedaluwarsa(string $tipe, DateTimeImmutable $batas, DateTimeImmutable $sekarang): void
    {
        DB::table('import_batches')
            ->where('tipe', $tipe)
            ->where('status', StatusBatch::Berjalan->value)
            ->where('mulai_at', '<', $batas->format(self::FORMAT))
            ->update([
                'status' => StatusBatch::Gagal->value,
                'selesai_at' => $sekarang->format(self::FORMAT),
                'ringkasan' => json_encode(['galat' => 'Sinkron terhenti sebelum selesai dan ditutup otomatis.']),
                'updated_at' => $sekarang->format(self::FORMAT),
            ]);
    }

    public function adaYangBerjalan(string $tipe): bool
    {
        return DB::table('import_batches')->where('tipe', $tipe)->where('status', StatusBatch::Berjalan->value)->exists();
    }

    public function selesai(int $id, StatusBatch $status, array $ringkasan, ?string $hashRespons, DateTimeImmutable $sekarang): void
    {
        DB::table('import_batches')->where('id', $id)->update([
            'status' => $status->value,
            'ringkasan' => json_encode($ringkasan, JSON_THROW_ON_ERROR),
            'hash_respons' => $hashRespons,
            'selesai_at' => $sekarang->format(self::FORMAT),
            'updated_at' => $sekarang->format(self::FORMAT),
        ]);
    }

    public function catatGalat(int $id, array $galat): void
    {
        $baris = array_map(fn (GalatBaris $g) => [
            'import_batch_id' => $id,
            'baris' => $g->baris,
            'kolom' => $g->kolom,
            'pesan' => mb_substr($g->pesan, 0, 255),
            'nilai' => $g->nilai,
        ], $galat);

        foreach (array_chunk($baris, 200) as $potongan) {
            DB::table('import_errors')->insert($potongan);
        }
    }

    public function terakhirBerhasil(string $tipe): ?RingkasBatch
    {
        $baris = DB::table('import_batches')
            ->where('tipe', $tipe)
            ->whereIn('status', [StatusBatch::Sukses->value, StatusBatch::Sebagian->value])
            ->orderByDesc('id')
            ->first();

        return $baris === null ? null : $this->petakan($baris);
    }

    public function terbaru(string $tipe, int $jumlah): array
    {
        return DB::table('import_batches')->where('tipe', $tipe)->orderByDesc('id')->limit($jumlah)->get()->map(fn ($b) => $this->petakan($b))->all();
    }

    public function cari(int $id): ?RingkasBatch
    {
        $baris = DB::table('import_batches')->where('id', $id)->first();

        return $baris === null ? null : $this->petakan($baris);
    }

    public function galat(int $id): array
    {
        return DB::table('import_errors')->where('import_batch_id', $id)->orderBy('baris')->get()
            ->map(fn ($g) => new GalatBaris((int) $g->baris, $g->kolom, $g->pesan, $g->nilai))->all();
    }

    private function petakan(object $baris): RingkasBatch
    {
        return new RingkasBatch(
            (int) $baris->id,
            $baris->tipe,
            StatusBatch::from($baris->status),
            $baris->mulai_at,
            $baris->selesai_at,
            $baris->ringkasan === null ? [] : (json_decode($baris->ringkasan, true) ?: []),
            $baris->hash_respons,
            $baris->sumber,
        );
    }
}
