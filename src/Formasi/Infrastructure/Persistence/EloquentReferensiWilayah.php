<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\BarisWilayah;
use Src\Formasi\Application\ReferensiWilayahRepository;
use Src\Formasi\Domain\Lokasi;
use Src\Formasi\Domain\NamaWilayah;
use Src\Formasi\Domain\TipeLokasi;

final class EloquentReferensiWilayah implements ReferensiWilayahRepository
{
    private const FORMAT_WAKTU = 'Y-m-d H:i:s';

    public function simpanBanyak(array $baris, DateTimeImmutable $sekarang): int
    {
        $waktu = $sekarang->format(self::FORMAT_WAKTU);

        DB::transaction(function () use ($baris, $waktu): void {
            $provinsi = array_values(array_filter($baris, fn (BarisWilayah $b) => $b->tingkat === TipeLokasi::Provinsi));
            $this->simpan($provinsi, [], $waktu);
            DB::table('wilayahs')->where('tingkat', TipeLokasi::Provinsi->value)->update(['provinsi_id' => DB::raw('id')]);

            $idProvinsi = DB::table('wilayahs')->where('tingkat', TipeLokasi::Provinsi->value)->whereNotNull('kode')->pluck('id', 'kode')->all();
            $lainnya = array_values(array_filter($baris, fn (BarisWilayah $b) => $b->tingkat !== TipeLokasi::Provinsi));
            $this->simpan($lainnya, $idProvinsi, $waktu);
        });

        return count($baris);
    }

    public function jumlahReferensi(): int
    {
        return DB::table('wilayahs')->whereNotNull('kode')->count();
    }

    public function petakanUlangFormasi(): int
    {
        $wilayah = [];

        foreach (DB::table('wilayahs')->whereNotNull('kode')->get(['id', 'tingkat', 'nama_normal', 'provinsi_id']) as $baris) {
            $wilayah[$baris->tingkat.'|'.$baris->nama_normal] = $baris;
        }

        $dipetakan = 0;
        $kandidat = DB::table('formasis')->whereNull('wilayah_id')->whereIn('tipe_lokasi', [TipeLokasi::Provinsi->value, TipeLokasi::Kabupaten->value, TipeLokasi::Kota->value])->distinct()->get(['tipe_lokasi', 'lokasi_teks']);

        foreach ($kandidat as $butir) {
            $lokasi = Lokasi::dari($butir->lokasi_teks);
            $temu = $wilayah[$lokasi->tipe->value.'|'.NamaWilayah::kunci($lokasi->nama)] ?? null;

            if ($temu !== null) {
                $dipetakan += DB::table('formasis')->whereNull('wilayah_id')->where('tipe_lokasi', $butir->tipe_lokasi)->where('lokasi_teks', $butir->lokasi_teks)->update(['wilayah_id' => $temu->id, 'provinsi_id' => $temu->provinsi_id]);
            }
        }

        return $dipetakan;
    }

    private function simpan(array $baris, array $idProvinsi, string $waktu): void
    {
        $data = array_map(fn (BarisWilayah $b) => [
            'kode' => $b->kode,
            'nama' => $b->nama,
            'nama_normal' => NamaWilayah::kunciReferensi($b->nama),
            'tingkat' => $b->tingkat->value,
            'provinsi_id' => $idProvinsi[$b->kodeProvinsi()] ?? null,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ], $baris);

        foreach (array_chunk($data, 100) as $potongan) {
            DB::table('wilayahs')->upsert($potongan, ['tingkat', 'nama_normal'], ['kode', 'nama', 'provinsi_id', 'updated_at']);
        }
    }
}
