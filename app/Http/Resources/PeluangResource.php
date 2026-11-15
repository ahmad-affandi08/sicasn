<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Formasi\Application\AngkaNasional;
use Src\Formasi\Application\HasilPeluang;
use Src\Formasi\Application\InstansiPeluang;
use Src\Formasi\Application\SnapshotStatistik;
use Src\Formasi\Domain\TingkatPersaingan;

final class PeluangResource
{
    public static function dari(HasilPeluang $hasil): array
    {
        return [
            'item' => array_map(fn (InstansiPeluang $i) => self::instansi($i) + ['formasiAktif' => $hasil->formasiAktif[$i->instansiId] ?? 0], $hasil->item),
            'paginasi' => ['total' => $hasil->total, 'halaman' => $hasil->halaman, 'terakhir' => $hasil->halamanTerakhir, 'perHalaman' => $hasil->perHalaman],
            'totalInstansi' => $hasil->totalInstansi,
            'nasional' => array_map(self::nasional(...), $hasil->nasional),
            'ambang' => ['longgarMaks' => $hasil->ambang->longgarMaks, 'ketatMin' => $hasil->ambang->ketatMin],
            'tingkat' => array_map(fn (TingkatPersaingan $t) => ['kode' => $t->value, 'label' => $t->label()], TingkatPersaingan::cases()),
        ];
    }

    public static function instansi(InstansiPeluang $instansi): array
    {
        return [
            'id' => $instansi->instansiId,
            'nama' => $instansi->nama,
            'tipe' => $instansi->tipe,
            'formasi' => $instansi->formasi,
            'formasiDariTanggal' => $instansi->formasiDipinjamDari,
            'pelamar' => $instansi->terbaru->pelamar,
            'submit' => $instansi->terbaru->submit,
            'rasio' => $instansi->penilaian->rasio === null ? null : round($instansi->penilaian->rasio, 1),
            'batasBawah' => $instansi->penilaian->batasBawah,
            'tingkat' => $instansi->penilaian->tingkat->value,
            'tingkatLabel' => $instansi->penilaian->tingkat->label(),
            'terbaru' => self::snapshot($instansi->terbaru),
            'riwayat' => array_map(self::snapshot(...), $instansi->riwayat),
        ];
    }

    public static function snapshot(SnapshotStatistik $snapshot): array
    {
        return [
            'tanggal' => $snapshot->tanggal,
            'status' => $snapshot->statusAngka->value,
            'formasi' => $snapshot->formasi,
            'pelamar' => $snapshot->pelamar,
            'submit' => $snapshot->submit,
            'lolosAdministrasi' => $snapshot->lolosAdministrasi,
            'tingkatData' => $snapshot->tingkatData->value,
            'keyakinan' => $snapshot->keyakinan->value,
            'sumberNama' => $snapshot->sumberNama,
            'sumberUrl' => $snapshot->sumberUrl,
            'catatan' => $snapshot->catatan,
        ];
    }

    public static function nasional(AngkaNasional $angka): array
    {
        return [
            'jenis' => $angka->jenis,
            'nilai' => $angka->nilai,
            'tanggal' => $angka->tanggal,
            'sumberNama' => $angka->sumberNama,
            'sumberUrl' => $angka->sumberUrl,
            'keyakinan' => $angka->keyakinan->value,
        ];
    }
}
