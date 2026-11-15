<?php

declare(strict_types=1);

namespace Src\Latihan\Infrastructure;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Latihan\Application\PercobaanRepository;
use Src\Latihan\Application\RekamPercobaan;
use Src\Latihan\Application\SoalDikerjakan;
use Src\Latihan\Domain\KategoriSoal;

final class DatabasePercobaanRepository implements PercobaanRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function buat(int $penggunaId, string $mode, DateTimeImmutable $mulai, DateTimeImmutable $batas, array $soalIds, DateTimeImmutable $sekarang): int
    {
        $waktu = $sekarang->format(self::FORMAT);

        return DB::transaction(function () use ($penggunaId, $mode, $mulai, $batas, $soalIds, $waktu): int {
            $id = DB::table('percobaans')->insertGetId([
                'user_id' => $penggunaId,
                'mode' => $mode,
                'mulai_at' => $mulai->format(self::FORMAT),
                'batas_selesai_at' => $batas->format(self::FORMAT),
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);

            DB::table('percobaan_soals')->insert(array_map(
                fn (int $soalId, int $urutan) => ['percobaan_id' => $id, 'soal_id' => $soalId, 'urutan' => $urutan + 1, 'created_at' => $waktu, 'updated_at' => $waktu],
                $soalIds,
                array_keys($soalIds),
            ));

            return $id;
        });
    }

    public function idBelumSelesai(int $penggunaId): ?int
    {
        $id = DB::table('percobaans')->where('user_id', $penggunaId)->whereNull('selesai_at')->orderByDesc('id')->value('id');

        return $id === null ? null : (int) $id;
    }

    public function cari(int $penggunaId, int $id): ?RekamPercobaan
    {
        $baris = DB::table('percobaans')->where('user_id', $penggunaId)->where('id', $id)->first();

        if ($baris === null) {
            return null;
        }

        return new RekamPercobaan(
            (int) $baris->id,
            (string) $baris->mode,
            new DateTimeImmutable((string) $baris->mulai_at),
            new DateTimeImmutable((string) $baris->batas_selesai_at),
            $baris->selesai_at === null ? null : new DateTimeImmutable((string) $baris->selesai_at),
        );
    }

    public function soalUntukDikerjakan(int $percobaanId): array
    {
        $opsiPerSoal = $this->opsiPerSoal($percobaanId, false);

        return DB::table('percobaan_soals as ps')
            ->join('soals as s', 's.id', '=', 'ps.soal_id')
            ->where('ps.percobaan_id', $percobaanId)
            ->orderBy('ps.urutan')
            ->get(['ps.urutan', 'ps.soal_id', 'ps.opsi_id', 'ps.ragu', 's.kategori', 's.subkategori', 's.pertanyaan'])
            ->map(fn (object $b) => new SoalDikerjakan(
                (int) $b->urutan,
                (int) $b->soal_id,
                (string) $b->kategori,
                (string) $b->subkategori,
                (string) $b->pertanyaan,
                $opsiPerSoal[(int) $b->soal_id] ?? [],
                $b->opsi_id === null ? null : (int) $b->opsi_id,
                (bool) $b->ragu,
            ))
            ->all();
    }

    public function simpanJawaban(int $percobaanId, int $soalId, ?int $opsiId, bool $ragu): bool
    {
        $kriteria = ['percobaan_id' => $percobaanId, 'soal_id' => $soalId];

        DB::table('percobaan_soals')->where($kriteria)->update(['opsi_id' => $opsiId, 'ragu' => $ragu, 'updated_at' => now()->format(self::FORMAT)]);

        return DB::table('percobaan_soals')->where($kriteria)->exists();
    }

    public function opsiMilikSoal(int $soalId, int $opsiId): bool
    {
        return DB::table('soal_opsis')->where('id', $opsiId)->where('soal_id', $soalId)->exists();
    }

    public function ringkasSkor(int $percobaanId): array
    {
        $ringkasan = [];

        foreach (KategoriSoal::cases() as $kategori) {
            $ringkasan[$kategori->value] = ['jumlah' => 0, 'skor' => 0];
        }

        $baris = DB::table('percobaan_soals as ps')
            ->join('soals as s', 's.id', '=', 'ps.soal_id')
            ->leftJoin('soal_opsis as o', 'o.id', '=', 'ps.opsi_id')
            ->where('ps.percobaan_id', $percobaanId)
            ->groupBy('s.kategori')
            ->selectRaw('s.kategori as kategori, count(*) as jumlah, coalesce(sum(o.skor), 0) as skor')
            ->get();

        foreach ($baris as $b) {
            $ringkasan[$b->kategori] = ['jumlah' => (int) $b->jumlah, 'skor' => (int) $b->skor];
        }

        return $ringkasan;
    }

    public function tandaiSelesai(int $id, DateTimeImmutable $selesai, array $ringkasan): void
    {
        DB::table('percobaans')->where('id', $id)->whereNull('selesai_at')->update([
            'selesai_at' => $selesai->format(self::FORMAT),
            'jumlah_twk' => $ringkasan['twk']['jumlah'],
            'jumlah_tiu' => $ringkasan['tiu']['jumlah'],
            'jumlah_tkp' => $ringkasan['tkp']['jumlah'],
            'skor_twk' => $ringkasan['twk']['skor'],
            'skor_tiu' => $ringkasan['tiu']['skor'],
            'skor_tkp' => $ringkasan['tkp']['skor'],
            'updated_at' => $selesai->format(self::FORMAT),
        ]);
    }

    public function soalDenganKunci(int $percobaanId): array
    {
        $opsiPerSoal = $this->opsiPerSoal($percobaanId, true);

        return DB::table('percobaan_soals as ps')
            ->join('soals as s', 's.id', '=', 'ps.soal_id')
            ->where('ps.percobaan_id', $percobaanId)
            ->orderBy('ps.urutan')
            ->get(['ps.urutan', 'ps.soal_id', 'ps.opsi_id', 'ps.ragu', 's.kategori', 's.subkategori', 's.pertanyaan', 's.pembahasan', 's.sumber_nama', 's.sumber_url', 's.lisensi'])
            ->map(fn (object $b) => [
                'urutan' => (int) $b->urutan,
                'kategori' => (string) $b->kategori,
                'subkategori' => (string) $b->subkategori,
                'pertanyaan' => (string) $b->pertanyaan,
                'pembahasan' => (string) $b->pembahasan,
                'sumberNama' => (string) $b->sumber_nama,
                'sumberUrl' => $b->sumber_url === null ? null : (string) $b->sumber_url,
                'lisensi' => (string) $b->lisensi,
                'opsi' => $opsiPerSoal[(int) $b->soal_id] ?? [],
                'opsiDipilihId' => $b->opsi_id === null ? null : (int) $b->opsi_id,
                'ragu' => (bool) $b->ragu,
            ])
            ->all();
    }

    public function riwayat(int $penggunaId, int $batas): array
    {
        return DB::table('percobaans')
            ->where('user_id', $penggunaId)
            ->whereNotNull('selesai_at')
            ->orderByDesc('selesai_at')
            ->orderByDesc('id')
            ->limit($batas)
            ->get()
            ->map(fn (object $b) => [
                'id' => (int) $b->id,
                'mode' => (string) $b->mode,
                'selesai' => (new DateTimeImmutable((string) $b->selesai_at))->format(DATE_ATOM),
                'jumlah' => ['twk' => (int) $b->jumlah_twk, 'tiu' => (int) $b->jumlah_tiu, 'tkp' => (int) $b->jumlah_tkp],
                'skor' => ['twk' => (int) $b->skor_twk, 'tiu' => (int) $b->skor_tiu, 'tkp' => (int) $b->skor_tkp],
            ])
            ->all();
    }

    public function idSoalTerbaru(int $penggunaId, int $jumlahPercobaan): array
    {
        $percobaanIds = DB::table('percobaans')->where('user_id', $penggunaId)->orderByDesc('id')->limit($jumlahPercobaan)->pluck('id');

        if ($percobaanIds->isEmpty()) {
            return [];
        }

        return DB::table('percobaan_soals')->whereIn('percobaan_id', $percobaanIds)->pluck('soal_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    private function opsiPerSoal(int $percobaanId, bool $denganSkor): array
    {
        $soalIds = DB::table('percobaan_soals')->where('percobaan_id', $percobaanId)->pluck('soal_id');
        $hasil = [];

        foreach (DB::table('soal_opsis')->whereIn('soal_id', $soalIds)->orderBy('soal_id')->orderBy('label')->get(['id', 'soal_id', 'label', 'teks', 'skor']) as $b) {
            $opsi = ['id' => (int) $b->id, 'label' => (string) $b->label, 'teks' => (string) $b->teks];
            $hasil[(int) $b->soal_id][] = $denganSkor ? $opsi + ['skor' => (int) $b->skor] : $opsi;
        }

        return $hasil;
    }
}
