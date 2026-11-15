<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\BarisStatistik;
use Src\Formasi\Application\HasilSimpanStatistik;
use Src\Formasi\Application\SnapshotStatistik;
use Src\Formasi\Application\StatistikRepository;
use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;
use Src\Formasi\Domain\TipeInstansi;

final class EloquentStatistikRepository implements StatistikRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    private const UKURAN_POTONGAN = 200;

    public function simpanBanyak(array $baris, array $idInstansi, int $batchId, DateTimeImmutable $sekarang): HasilSimpanStatistik
    {
        $waktu = $sekarang->format(self::FORMAT);
        $baru = 0;
        $duplikat = 0;

        foreach (array_chunk($baris, self::UKURAN_POTONGAN) as $potongan) {
            $siap = [];

            foreach ($potongan as $butir) {
                $instansiId = $butir->cakupan === Cakupan::Instansi ? ($idInstansi[NamaNormal::dari($butir->namaInstansi)] ?? null) : null;
                $kunci = $butir->kunci($instansiId);
                $siap[$kunci] = $this->baris($butir, $kunci, $instansiId, $batchId, $waktu);
            }

            $ada = DB::table('statistik_pelamars')->whereIn('kunci', array_keys($siap))->pluck('kunci')->all();
            $duplikat += count($potongan) - count($siap) + count($ada);

            $tambah = array_values(array_diff_key($siap, array_flip($ada)));

            if ($tambah !== []) {
                DB::table('statistik_pelamars')->insert($tambah);
                $baru += count($tambah);
            }
        }

        return new HasilSimpanStatistik($baru, $duplikat);
    }

    public function hapusBatch(int $batchId): int
    {
        return DB::table('statistik_pelamars')->where('import_batch_id', $batchId)->delete();
    }

    public function snapshot(int $tahun, JenisPengadaan $jenis, Cakupan $cakupan, ?int $instansiId = null): array
    {
        return DB::table('statistik_pelamars as s')
            ->leftJoin('instansis as i', 'i.id', '=', 's.instansi_id')
            ->where('s.tahun', $tahun)
            ->where('s.jenis_pengadaan', $jenis->value)
            ->where('s.cakupan', $cakupan->value)
            ->when($instansiId !== null, fn ($q) => $q->where('s.instansi_id', $instansiId))
            ->orderByDesc('s.tanggal_snapshot')
            ->orderBy('s.id')
            ->get(['s.*', 'i.nama as nama_instansi', 'i.tipe as tipe_instansi'])
            ->map(fn (object $b) => new SnapshotStatistik(
                $b->instansi_id === null ? null : (int) $b->instansi_id,
                $b->nama_instansi,
                TipeInstansi::tryFrom((string) $b->tipe_instansi) ?? TipeInstansi::TidakDiketahui,
                (string) $b->tanggal_snapshot,
                StatusAngka::from($b->status_angka),
                $b->jumlah_formasi === null ? null : (int) $b->jumlah_formasi,
                $b->jumlah_pelamar === null ? null : (int) $b->jumlah_pelamar,
                $b->jumlah_submit === null ? null : (int) $b->jumlah_submit,
                $b->jumlah_lolos_administrasi === null ? null : (int) $b->jumlah_lolos_administrasi,
                TingkatData::from($b->tingkat_data),
                Keyakinan::from($b->keyakinan),
                $b->sumber_nama,
                $b->sumber_url,
                $b->catatan,
            ))->all();
    }

    public function jumlahBaris(): int
    {
        return DB::table('statistik_pelamars')->count();
    }

    private function baris(BarisStatistik $b, string $kunci, ?int $instansiId, int $batchId, string $waktu): array
    {
        return [
            'kunci' => $kunci,
            'tahun' => $b->tahun,
            'jenis_pengadaan' => $b->jenisPengadaan->value,
            'cakupan' => $b->cakupan->value,
            'instansi_id' => $instansiId,
            'jumlah_formasi' => $b->jumlahFormasi,
            'jumlah_pelamar' => $b->jumlahPelamar,
            'jumlah_submit' => $b->jumlahSubmit,
            'jumlah_lolos_administrasi' => $b->jumlahLolosAdministrasi,
            'tanggal_snapshot' => $b->tanggalSnapshot,
            'status_angka' => $b->statusAngka->value,
            'tingkat_data' => $b->tingkatData->value,
            'keyakinan' => $b->keyakinan->value,
            'sumber_nama' => $b->sumberNama,
            'sumber_url' => $b->sumberUrl,
            'catatan' => $b->catatan,
            'import_batch_id' => $batchId,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ];
    }
}
