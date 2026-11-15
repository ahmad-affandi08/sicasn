<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Formasi\Application\DetailFormasi;
use Src\Formasi\Application\HasilPencarianFormasi;
use Src\Formasi\Application\ItemFormasi;
use Src\Formasi\Application\OpsiFormasi;
use Src\Formasi\Application\OpsiPilihan;
use Src\Formasi\Application\PeluangFormasi;
use Src\Formasi\Application\PeluangJabatan;
use Src\Formasi\Application\RingkasFormasi;
use Src\Formasi\Application\SnapshotJabatan;
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
            'peluangJabatan' => self::peluangJabatan($detail->peluangJabatan),
            'riwayatJabatan' => array_map(self::snapshotJabatan(...), $detail->riwayatJabatan),
            'riwayat' => array_map(PeluangResource::snapshot(...), $detail->riwayat),
            'tautanResmi' => $detail->tautanResmi,
            'ambang' => ['longgarMaks' => $detail->ambang->longgarMaks, 'ketatMin' => $detail->ambang->ketatMin],
        ];
    }

    public static function item(ItemFormasi $item): array
    {
        return self::ringkas($item->formasi) + ['peluang' => self::peluang($item->peluang), 'peluangJabatan' => self::peluangJabatan($item->peluangJabatan)];
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

    private static function peluangJabatan(?PeluangJabatan $peluang): ?array
    {
        if ($peluang === null) {
            return null;
        }

        $d = $peluang->dasar;

        return [
            'tingkat' => $peluang->penilaian->tingkat->value,
            'tingkatLabel' => $peluang->penilaian->tingkat->label(),
            'rasio' => $peluang->penilaian->rasio === null ? null : round($peluang->penilaian->rasio, 1),
            'batasBawah' => $peluang->penilaian->batasBawah,
            'formasi' => $d->formasi,
            'pelamar' => $d->pelamar,
            'submit' => $d->submit,
            'lolosAdministrasi' => $d->lolosAdministrasi,
            'lokasi' => $d->lokasi,
            'statusAngka' => $d->statusAngka->value,
            'tingkatData' => $d->tingkatData->value,
            'keyakinan' => $d->keyakinan->value,
            'tanggal' => $d->tanggal,
            'sumberNama' => $d->sumberNama,
            'sumberUrl' => $d->sumberUrl,
            'catatan' => $d->catatan,
        ];
    }

    private static function snapshotJabatan(SnapshotJabatan $s): array
    {
        return [
            'tanggal' => $s->tanggal,
            'status' => $s->statusAngka->value,
            'formasi' => $s->formasi,
            'pelamar' => $s->pelamar,
            'submit' => $s->submit,
            'lolosAdministrasi' => $s->lolosAdministrasi,
            'tingkatData' => $s->tingkatData->value,
            'keyakinan' => $s->keyakinan->value,
            'sumberNama' => $s->sumberNama,
            'sumberUrl' => $s->sumberUrl,
            'catatan' => $s->catatan,
            'lokasi' => $s->lokasi,
        ];
    }

    public static function pilihan(OpsiPilihan $opsi): array
    {
        return ['nilai' => $opsi->nilai, 'label' => $opsi->label, 'jumlah' => $opsi->jumlah];
    }
}
