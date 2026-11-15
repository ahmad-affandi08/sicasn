<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\OpsiFormasiRepository;
use Src\Formasi\Application\OpsiPilihan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NamaNormal;

final class EloquentOpsiFormasi implements OpsiFormasiRepository
{
    public function jenjang(array $jenis): array
    {
        return DB::table('kualifikasi_set_items as ki')
            ->whereIn('ki.kualifikasi_set_id', $this->setAktif($jenis))
            ->distinct()
            ->pluck('ki.jenjang')
            ->all();
    }

    public function provinsi(array $jenis): array
    {
        return $this->hitungWilayah('f.provinsi_id', $jenis)->map(fn (object $b) => new OpsiPilihan((string) $b->id, $b->nama, (int) $b->jumlah))->all();
    }

    public function kabupatenKota(int $provinsiId, array $jenis): array
    {
        return DB::table('formasis as f')
            ->join('wilayahs as w', 'w.id', '=', 'f.wilayah_id')
            ->where('f.status', 'aktif')
            ->whereIn('f.jenis_pengadaan', $this->nilai($jenis))
            ->where('f.provinsi_id', $provinsiId)
            ->where('w.tingkat', '!=', 'provinsi')
            ->groupBy('w.id', 'w.nama')
            ->orderBy('w.nama')
            ->get(['w.id', 'w.nama', DB::raw('count(*) as jumlah')])
            ->map(fn (object $b) => new OpsiPilihan((string) $b->id, $b->nama, (int) $b->jumlah))
            ->all();
    }

    public function jenisFormasi(array $jenis): array
    {
        return DB::table('formasis')
            ->where('status', 'aktif')
            ->whereIn('jenis_pengadaan', $this->nilai($jenis))
            ->groupBy('jenis_formasi')
            ->orderBy('jenis_formasi')
            ->get(['jenis_formasi', DB::raw('count(*) as jumlah')])
            ->map(fn (object $b) => new OpsiPilihan($b->jenis_formasi, $b->jenis_formasi, (int) $b->jumlah))
            ->all();
    }

    public function instansi(string $kataKunci, array $jenis, int $batas): array
    {
        $kata = NamaNormal::dari($kataKunci);

        return DB::table('instansis as i')
            ->whereIn('i.id', DB::table('formasis')->select('instansi_id')->where('status', 'aktif')->whereIn('jenis_pengadaan', $this->nilai($jenis)))
            ->when($kata !== '', fn (Builder $q) => $q->where('i.nama_normal', 'like', '%'.addcslashes($kata, '%_\\').'%'))
            ->orderBy('i.nama')
            ->limit($batas)
            ->get(['i.id', 'i.nama'])
            ->map(fn (object $b) => new OpsiPilihan((string) $b->id, $b->nama))
            ->all();
    }

    public function jurusan(string $kataKunci, array $jenis, int $batas): array
    {
        $kata = NamaNormal::dari($kataKunci);

        return DB::table('jurusans as ju')
            ->whereIn('ju.id', DB::table('kualifikasi_set_items')->select('jurusan_id')->whereNotNull('jurusan_id')->whereIn('kualifikasi_set_id', $this->setAktif($jenis)))
            ->when($kata !== '', fn (Builder $q) => $q->where('ju.nama_normal', 'like', '%'.addcslashes($kata, '%_\\').'%'))
            ->orderBy('ju.nama')
            ->limit($batas)
            ->get(['ju.id', 'ju.nama'])
            ->map(fn (object $b) => new OpsiPilihan((string) $b->id, $b->nama))
            ->all();
    }

    public function namaInstansi(int $id): ?string
    {
        return DB::table('instansis')->where('id', $id)->value('nama');
    }

    public function jurusanTerpilih(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('jurusans')->whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama'])
            ->map(fn (object $b) => new OpsiPilihan((string) $b->id, $b->nama))
            ->all();
    }

    private function setAktif(array $jenis): Builder
    {
        return DB::table('formasis')->select('kualifikasi_set_id')->whereNotNull('kualifikasi_set_id')->where('status', 'aktif')->whereIn('jenis_pengadaan', $this->nilai($jenis));
    }

    private function hitungWilayah(string $kolom, array $jenis)
    {
        return DB::table('formasis as f')
            ->join('wilayahs as w', 'w.id', '=', $kolom)
            ->where('f.status', 'aktif')
            ->whereIn('f.jenis_pengadaan', $this->nilai($jenis))
            ->groupBy('w.id', 'w.nama')
            ->orderBy('w.nama')
            ->get(['w.id', 'w.nama', DB::raw('count(*) as jumlah')]);
    }

    private function nilai(array $jenis): array
    {
        return array_map(fn (JenisPengadaan $j) => $j->value, $jenis);
    }
}
