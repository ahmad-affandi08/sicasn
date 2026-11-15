<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\InstansiPeluang;
use Src\Formasi\Application\PeluangInstansiRepository;
use Src\Formasi\Application\PeluangMentah;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

final class EloquentPeluangInstansiRepository implements PeluangInstansiRepository
{
    public function ganti(array $daftar, DateTimeImmutable $sekarang): int
    {
        $waktu = $sekarang->format('Y-m-d H:i:s');

        $baris = array_map(fn (InstansiPeluang $i) => [
            'instansi_id' => $i->instansiId,
            'pelamar' => $i->terbaru->pelamar,
            'submit' => $i->terbaru->submit,
            'formasi' => $i->formasi,
            'formasi_dari_tanggal' => $i->formasiDipinjamDari,
            'status_angka' => $i->terbaru->statusAngka->value,
            'tingkat_data' => $i->terbaru->tingkatData->value,
            'keyakinan' => $i->terbaru->keyakinan->value,
            'tanggal_snapshot' => $i->terbaru->tanggal,
            'sumber_nama' => $i->terbaru->sumberNama,
            'sumber_url' => $i->terbaru->sumberUrl,
            'rasio' => $i->penilaian->rasio,
            'golongan_rasio' => $i->golonganRasio(),
            'pelamar_dasar' => $i->pelamarDasar(),
            'golongan_pelamar' => $i->golonganPelamar(),
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ], $daftar);

        DB::transaction(function () use ($baris): void {
            DB::table('peluang_instansis')->delete();

            foreach (array_chunk($baris, 200) as $potongan) {
                DB::table('peluang_instansis')->insert($potongan);
            }
        });

        return count($baris);
    }

    public function semua(): array
    {
        $hasil = [];

        foreach (DB::table('peluang_instansis')->get() as $b) {
            $hasil[(int) $b->instansi_id] = new PeluangMentah(
                (int) $b->instansi_id,
                $b->pelamar === null ? null : (int) $b->pelamar,
                $b->submit === null ? null : (int) $b->submit,
                $b->formasi === null ? null : (int) $b->formasi,
                $b->formasi_dari_tanggal,
                StatusAngka::from($b->status_angka),
                TingkatData::from($b->tingkat_data),
                Keyakinan::from($b->keyakinan),
                (string) $b->tanggal_snapshot,
                $b->sumber_nama,
                $b->sumber_url,
            );
        }

        return $hasil;
    }
}
