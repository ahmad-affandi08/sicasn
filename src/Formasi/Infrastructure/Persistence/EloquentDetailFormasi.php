<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\DetailFormasiRepository;
use Src\Formasi\Application\RincianFormasi;
use Src\Formasi\Application\RingkasFormasi;
use Src\Formasi\Domain\JenisPengadaan;

final class EloquentDetailFormasi implements DetailFormasiRepository
{
    public function cari(int $id): ?RincianFormasi
    {
        $f = DB::table('formasis as f')
            ->join('instansis as i', 'i.id', '=', 'f.instansi_id')
            ->join('jabatans as j', 'j.id', '=', 'f.jabatan_id')
            ->leftJoin('wilayahs as w', 'w.id', '=', 'f.wilayah_id')
            ->leftJoin('wilayahs as pv', 'pv.id', '=', 'f.provinsi_id')
            ->leftJoin('kualifikasi_sets as ks', 'ks.id', '=', 'f.kualifikasi_set_id')
            ->where('f.id', $id)
            ->first(['f.*', 'i.nama as instansi', 'j.nama as jabatan', 'j.kode as kode_jabatan', 'j.nama_normal as jabatan_normal', 'w.nama as wilayah', 'pv.nama as provinsi', 'ks.teks as kualifikasi']);

        if ($f === null) {
            return null;
        }

        return new RincianFormasi(
            new RingkasFormasi(
                (int) $f->id,
                (int) $f->instansi_id,
                $f->instansi,
                $f->jabatan,
                (string) $f->kode_jabatan,
                $f->jabatan_normal,
                $f->lokasi_teks,
                $f->wilayah,
                $f->wilayah_id === null ? null : (int) $f->wilayah_id,
                (int) $f->kuota,
                $f->jenis_formasi,
                $f->is_disabilitas === null ? null : (bool) $f->is_disabilitas,
                $f->usia_min === null ? null : (int) $f->usia_min,
                $f->usia_max === null ? null : (int) $f->usia_max,
                (bool) $f->kualifikasi_tersedia,
                $f->kualifikasi,
            ),
            $f->status,
            $f->nama_event,
            JenisPengadaan::tryFrom($f->jenis_pengadaan)?->label() ?? $f->jenis_pengadaan,
            (int) $f->periode,
            $f->tipe_lokasi,
            $f->provinsi,
            $f->penghasilan_min === null ? null : (int) $f->penghasilan_min,
            $f->penghasilan_max === null ? null : (int) $f->penghasilan_max,
            $f->jobdesc,
            $this->kualifikasi($f->kualifikasi_set_id),
            $this->persyaratan((int) $f->id),
            (string) $f->first_seen_at,
            (string) $f->last_seen_at,
        );
    }

    private function kualifikasi(?int $setId): array
    {
        if ($setId === null) {
            return [];
        }

        return DB::table('kualifikasi_set_items as ki')
            ->leftJoin('jurusans as ju', 'ju.id', '=', 'ki.jurusan_id')
            ->where('ki.kualifikasi_set_id', $setId)
            ->orderBy('ki.id')
            ->get(['ki.jenjang', 'ju.nama as jurusan', 'ki.semua_jurusan'])
            ->map(fn (object $b) => ['jenjang' => $b->jenjang, 'jurusan' => $b->jurusan, 'semuaJurusan' => (bool) $b->semua_jurusan])
            ->all();
    }

    private function persyaratan(int $formasiId): array
    {
        return DB::table('formasi_persyaratans as fp')
            ->join('persyaratans as p', 'p.id', '=', 'fp.persyaratan_id')
            ->where('fp.formasi_id', $formasiId)
            ->orderBy('fp.urutan')
            ->get(['p.uraian', 'fp.wajib'])
            ->map(fn (object $b) => ['uraian' => $b->uraian, 'wajib' => (bool) $b->wajib])
            ->all();
    }
}
