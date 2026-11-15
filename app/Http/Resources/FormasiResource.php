<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Formasi\Application\DetailFormasi;
use Src\Formasi\Application\HasilPencarianFormasi;
use Src\Formasi\Application\ItemFormasi;
use Src\Formasi\Application\OpsiFormasi;
use Src\Formasi\Application\OpsiPilihan;
use Src\Formasi\Application\PeluangFormasi;
use Src\Formasi\Application\RingkasFormasi;
use Src\Formasi\Domain\TingkatPersaingan;

final class FormasiResource
{
    public static function halaman(HasilPencarianFormasi $hasil, OpsiFormasi $opsi): array
    {
        return [
            'item' => array_map(self::item(...), $hasil->item),
            'paginasi' => ['total' => $hasil->total, 'halaman' => $hasil->halaman, 'terakhir' => $hasil->halamanTerakhir, 'perHalaman' => $hasil->perHalaman, 'dibatasi' => $hasil->dibatasi],
            'totalAktif' => $hasil->totalAktif,
            'ambang' => ['longgarMaks' => $hasil->ambang->longgarMaks, 'ketatMin' => $hasil->ambang->ketatMin],
            'tingkat' => array_map(fn (TingkatPersaingan $t) => ['kode' => $t->value, 'label' => $t->label()], TingkatPersaingan::cases()),
            'opsi' => [
                'jenjang' => $opsi->jenjang,
                'provinsi' => array_map(self::pilihan(...), $opsi->provinsi),
                'kabupatenKota' => array_map(self::pilihan(...), $opsi->kabupatenKota),
                'jenisFormasi' => array_map(self::pilihan(...), $opsi->jenisFormasi),
                'instansiTerpilih' => $opsi->instansiTerpilih === null ? null : self::pilihan($opsi->instansiTerpilih),
                'jurusanTerpilih' => array_map(self::pilihan(...), $opsi->jurusanTerpilih),
            ],
        ];
    }

    public static function detail(DetailFormasi $detail): array
    {
        $r = $detail->rincian;

        return [
            'formasi' => self::ringkas($r->ringkas) + [
                'status' => $r->status,
                'namaEvent' => $r->namaEvent,
                'jenisPengadaan' => $r->jenisPengadaan,
                'periode' => $r->periode,
                'tipeLokasi' => $r->tipeLokasi,
                'provinsi' => $r->provinsi,
                'penghasilanMin' => $r->penghasilanMin,
                'penghasilanMaks' => $r->penghasilanMaks,
                'uraianTugas' => $r->uraianTugas,
                'kualifikasiItem' => $r->kualifikasi,
                'persyaratan' => $r->persyaratan,
                'pertamaKaliTerlihat' => $r->pertamaKaliTerlihat,
                'terakhirTerlihat' => $r->terakhirTerlihat,
            ],
            'peluang' => self::peluang($detail->peluang),
            'riwayat' => array_map(PeluangResource::snapshot(...), $detail->riwayat),
            'tautanResmi' => $detail->tautanResmi,
        ];
    }

    public static function item(ItemFormasi $item): array
    {
        return self::ringkas($item->formasi) + ['peluang' => self::peluang($item->peluang)];
    }

    private static function ringkas(RingkasFormasi $f): array
    {
        return [
            'id' => $f->id,
            'instansiId' => $f->instansiId,
            'instansi' => $f->instansi,
            'jabatan' => $f->jabatan,
            'kodeJabatan' => $f->kodeJabatan,
            'lokasi' => $f->wilayah ?? $f->lokasi,
            'lokasiTeks' => $f->lokasi,
            'kuota' => $f->kuota,
            'jenisFormasi' => $f->jenisFormasi,
            'disabilitas' => $f->disabilitas,
            'usiaMin' => $f->usiaMin,
            'usiaMaks' => $f->usiaMaks,
            'kualifikasiTersedia' => $f->kualifikasiTersedia,
            'kualifikasi' => $f->kualifikasi,
        ];
    }

    private static function peluang(?PeluangFormasi $peluang): ?array
    {
        if ($peluang === null) {
            return null;
        }

        return [
            'tingkat' => $peluang->penilaian->tingkat->value,
            'tingkatLabel' => $peluang->penilaian->tingkat->label(),
            'rasio' => $peluang->penilaian->rasio === null ? null : round($peluang->penilaian->rasio, 1),
            'batasBawah' => $peluang->penilaian->batasBawah,
            'pelamar' => $peluang->dasar->pelamar,
            'submit' => $peluang->dasar->submit,
            'formasi' => $peluang->dasar->formasi,
            'formasiDariTanggal' => $peluang->dasar->formasiDariTanggal,
            'statusAngka' => $peluang->dasar->statusAngka->value,
            'tingkatData' => $peluang->dasar->tingkatData->value,
            'keyakinan' => $peluang->dasar->keyakinan->value,
            'tanggal' => $peluang->dasar->tanggalSnapshot,
            'sumberNama' => $peluang->dasar->sumberNama,
            'sumberUrl' => $peluang->dasar->sumberUrl,
            'perJabatan' => false,
        ];
    }

    public static function pilihan(OpsiPilihan $opsi): array
    {
        return ['nilai' => $opsi->nilai, 'label' => $opsi->label, 'jumlah' => $opsi->jumlah];
    }
}
