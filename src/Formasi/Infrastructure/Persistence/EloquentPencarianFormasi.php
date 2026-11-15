<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\FilterFormasi;
use Src\Formasi\Application\HalamanFormasi;
use Src\Formasi\Application\KebijakanFormasiAktif;
use Src\Formasi\Application\PencarianFormasi;
use Src\Formasi\Application\RingkasFormasi;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NamaNormal;

final class EloquentPencarianFormasi implements PencarianFormasi
{
    private const BATAS_DAFTAR_ID = 1000;

    public function __construct(private readonly KebijakanFormasiAktif $kebijakan) {}

    public function cari(FilterFormasi $filter, ?array $instansiDiizinkan, ?array $instansiDikecualikan): HalamanFormasi
    {
        $total = $this->dasar($filter, $instansiDiizinkan, $instansiDikecualikan)->count();

        if ($total === 0) {
            return new HalamanFormasi([], 0);
        }

        $perHalaman = max(1, $filter->perHalaman);
        $halaman = min(max(1, $filter->halaman), max(1, (int) ceil($total / $perHalaman)), FilterFormasi::HALAMAN_MAKS);

        $id = $this->urutkan($this->dasar($filter, $instansiDiizinkan, $instansiDikecualikan), $filter->urut)->forPage($halaman, $perHalaman)->pluck('f.id')->map(fn ($nilai) => (int) $nilai)->all();

        return new HalamanFormasi($this->muatRingkas($id), $total);
    }

    private function muatRingkas(array $id): array
    {
        $baris = DB::table('formasis as f')
            ->join('instansis as i', 'i.id', '=', 'f.instansi_id')
            ->join('jabatans as j', 'j.id', '=', 'f.jabatan_id')
            ->leftJoin('wilayahs as w', 'w.id', '=', 'f.wilayah_id')
            ->leftJoin('kualifikasi_sets as ks', 'ks.id', '=', 'f.kualifikasi_set_id')
            ->whereIn('f.id', $id)
            ->get(['f.id', 'f.instansi_id', 'i.nama as instansi', 'j.nama as jabatan', 'j.kode as kode_jabatan', 'j.nama_normal as jabatan_normal', 'f.lokasi_teks', 'f.wilayah_id', 'w.nama as wilayah', 'f.kuota', 'f.jenis_formasi', 'f.is_disabilitas', 'f.usia_min', 'f.usia_max', 'f.kualifikasi_tersedia', 'ks.teks as kualifikasi'])
            ->keyBy('id');

        $hasil = [];

        foreach ($id as $nilai) {
            $b = $baris->get($nilai);

            if ($b !== null) {
                $hasil[] = new RingkasFormasi(
                    (int) $b->id,
                    (int) $b->instansi_id,
                    $b->instansi,
                    $b->jabatan,
                    (string) $b->kode_jabatan,
                    $b->jabatan_normal,
                    $b->lokasi_teks,
                    $b->wilayah,
                    $b->wilayah_id === null ? null : (int) $b->wilayah_id,
                    (int) $b->kuota,
                    $b->jenis_formasi,
                    $b->is_disabilitas === null ? null : (bool) $b->is_disabilitas,
                    $b->usia_min === null ? null : (int) $b->usia_min,
                    $b->usia_max === null ? null : (int) $b->usia_max,
                    (bool) $b->kualifikasi_tersedia,
                    $b->kualifikasi,
                );
            }
        }

        return $hasil;
    }

    private function dasar(FilterFormasi $filter, ?array $diizinkan, ?array $dikecualikan): Builder
    {
        $kueri = DB::table('formasis as f')
            ->where('f.status', 'aktif')
            ->whereIn('f.jenis_pengadaan', array_map(fn (JenisPengadaan $j) => $j->value, $this->kebijakan->aktif()));

        $this->saringTeks($kueri, $filter->cari);
        $this->saringKualifikasi($kueri, $filter);

        return $kueri
            ->when($filter->provinsi !== null, fn (Builder $q) => $q->where('f.provinsi_id', $filter->provinsi))
            ->when($filter->wilayah !== null, fn (Builder $q) => $q->where('f.wilayah_id', $filter->wilayah))
            ->when($filter->instansi !== null, fn (Builder $q) => $q->where('f.instansi_id', $filter->instansi))
            ->when($filter->jenisFormasi !== null && $filter->jenisFormasi !== '', fn (Builder $q) => $q->where('f.jenis_formasi', $filter->jenisFormasi))
            ->when($diizinkan !== null, fn (Builder $q) => $q->whereIn('f.instansi_id', $diizinkan))
            ->when($dikecualikan !== null && $dikecualikan !== [], fn (Builder $q) => $q->whereNotIn('f.instansi_id', $dikecualikan));
    }

    private function saringTeks(Builder $kueri, ?string $cari): void
    {
        $kata = NamaNormal::dari($cari);

        if ($kata === '') {
            return;
        }

        $pola = '%'.addcslashes($kata, '%_\\').'%';

        $jabatan = $this->idCocok('jabatans', $pola);
        $instansi = $this->idCocok('instansis', $pola);

        $kueri->where(fn (Builder $q) => $q->whereIn('f.jabatan_id', $jabatan)->orWhereIn('f.instansi_id', $instansi));
    }

    private function idCocok(string $tabel, string $pola): array|Builder
    {
        $kueri = DB::table($tabel)->where('nama_normal', 'like', $pola);
        $id = (clone $kueri)->limit(self::BATAS_DAFTAR_ID + 1)->pluck('id')->all();

        return count($id) > self::BATAS_DAFTAR_ID ? $kueri->select('id') : $id;
    }

    private function saringKualifikasi(Builder $kueri, FilterFormasi $filter): void
    {
        if (! $filter->menyaringKualifikasi()) {
            return;
        }

        $setCocok = DB::table('kualifikasi_set_items as ki')
            ->select('ki.kualifikasi_set_id')
            ->when($filter->jenjang !== [], fn (Builder $q) => $q->whereIn('ki.jenjang', $filter->jenjang))
            ->when($filter->jurusan !== [], fn (Builder $q) => $q->where(fn (Builder $j) => $j->where('ki.semua_jurusan', true)->orWhereIn('ki.jurusan_id', $filter->jurusan)));

        $kueri->where(function (Builder $q) use ($setCocok, $filter): void {
            $q->whereIn('f.kualifikasi_set_id', $setCocok);

            if ($filter->sertakanTanpaKualifikasi) {
                $q->orWhere('f.kualifikasi_tersedia', false);
            }
        });
    }

    private function urutkan(Builder $kueri, string $urut): Builder
    {
        return match ($urut) {
            'kuota_terbanyak' => $kueri->orderByDesc('f.kuota')->orderBy('f.id'),
            'terbaru' => $kueri->orderByDesc('f.first_seen_at')->orderByDesc('f.id'),
            'pelamar_tersedikit' => $kueri->leftJoin('peluang_instansis as p', 'p.instansi_id', '=', 'f.instansi_id')->orderByRaw('COALESCE(p.golongan_pelamar, 3)')->orderBy('p.pelamar_dasar')->orderByDesc('f.kuota')->orderBy('f.id'),
            default => $kueri->leftJoin('peluang_instansis as p', 'p.instansi_id', '=', 'f.instansi_id')->orderByRaw('COALESCE(p.golongan_rasio, 3)')->orderBy('p.rasio')->orderByDesc('f.kuota')->orderBy('f.id'),
        };
    }
}
