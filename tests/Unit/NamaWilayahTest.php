<?php

declare(strict_types=1);

use Src\Formasi\Application\PenguraiCsvWilayah;
use Src\Formasi\Domain\Exception\ReferensiWilayahRusak;
use Src\Formasi\Domain\Lokasi;
use Src\Formasi\Domain\NamaWilayah;
use Src\Formasi\Domain\TipeLokasi;

it('menyamakan penulisan BKN dengan nama referensi Kemendagri', function (string $bkn, string $referensi): void {
    expect(NamaWilayah::kunci($bkn))->toBe(NamaWilayah::kunciReferensi($referensi));
})->with([
    'spasi berbeda' => ['Palangka Raya', 'Kota Palangkaraya'],
    'tanpa spasi' => ['Tanjungpinang', 'Kota Tanjung Pinang'],
    'tanda hubung' => ['Tojo Una-Una', 'Kabupaten Tojo Una Una'],
    'kata dan' => ['Pangkajene Kepulauan', 'Kabupaten Pangkajene dan Kepulauan'],
    'padang sidimpuan' => ['Padang Sidimpuan', 'Kota Padangsidimpuan'],
    'singkatan DKI' => ['DKI Jakarta', 'Daerah Khusus Ibukota Jakarta'],
    'singkatan DI' => ['DI Yogyakarta', 'Daerah Istimewa Yogyakarta'],
    'kota administrasi' => ['Jakarta Pusat', 'Kota Administrasi Jakarta Pusat'],
    'huruf besar kecil' => ['SLEMAN', 'Kabupaten Sleman'],
]);

it('tidak menyamakan kabupaten dan kota yang namanya serupa lewat kunci saja', function (): void {
    expect(NamaWilayah::kunci('Bandung'))->toBe(NamaWilayah::kunciReferensi('Kota Bandung'))->toBe(NamaWilayah::kunciReferensi('Kabupaten Bandung'));
});

it('hanya mengupas satu awalan pada nama referensi', function (): void {
    expect(NamaWilayah::kunciReferensi('Kabupaten Kota Waringin Barat'))->toBe('kotawaringinbarat');
});

it('mengurai CSV referensi wilayah', function (): void {
    $baris = PenguraiCsvWilayah::urai("kode,nama,tingkat\n11,Aceh,provinsi\n11.01,Kabupaten Aceh Selatan,kabupaten\n11.71,Kota Banda Aceh,kota\n");

    expect($baris)->toHaveCount(3)
        ->and($baris[1]->kode)->toBe('11.01')
        ->and($baris[1]->kodeProvinsi())->toBe('11')
        ->and($baris[2]->tingkat)->toBe(TipeLokasi::Kota);
});

it('menolak referensi wilayah yang rusak', function (string $isi): void {
    expect(fn () => PenguraiCsvWilayah::urai($isi))->toThrow(ReferensiWilayahRusak::class);
})->with([
    'header salah' => ["nama,kode\nAceh,11\n"],
    'kode salah' => ["kode,nama,tingkat\nXX,Aceh,provinsi\n"],
    'tingkat salah' => ["kode,nama,tingkat\n11,Aceh,desa\n"],
    'tingkat satuan kerja' => ["kode,nama,tingkat\n11,Aceh,satuan_kerja\n"],
    'nama kosong' => ["kode,nama,tingkat\n11,,provinsi\n"],
]);

it('memetakan semua lokasi geografis nyata dari API BKN ke referensi', function (): void {
    $referensi = PenguraiCsvWilayah::urai((string) file_get_contents(__DIR__.'/../../database/seeders/data/wilayah_kemendagri.csv'));
    $kunci = [];

    foreach ($referensi as $baris) {
        $kunci[$baris->tingkat->value.'|'.NamaWilayah::kunciReferensi($baris->nama)] = true;
    }

    $teks = array_filter(explode("\n", (string) file_get_contents(__DIR__.'/../Fixtures/lokasi-bkn-nyata.txt')));
    $geografis = array_filter(array_map(Lokasi::dari(...), $teks), fn (Lokasi $l) => $l->terpetakanKeWilayah());
    $tidakCocok = array_filter($geografis, fn (Lokasi $l) => ! isset($kunci[$l->tipe->value.'|'.NamaWilayah::kunci($l->nama)]));

    expect($geografis)->not->toBeEmpty()->and(array_map(fn (Lokasi $l) => $l->teksAsli, $tidakCocok))->toBe([]);
});

it('memuat referensi berisi 38 provinsi dan 514 kabupaten atau kota dengan kunci unik', function (): void {
    $referensi = PenguraiCsvWilayah::urai((string) file_get_contents(__DIR__.'/../../database/seeders/data/wilayah_kemendagri.csv'));
    $kunci = array_map(fn ($b) => $b->tingkat->value.'|'.NamaWilayah::kunciReferensi($b->nama), $referensi);

    expect($referensi)->toHaveCount(552)
        ->and(count(array_filter($referensi, fn ($b) => $b->tingkat === TipeLokasi::Provinsi)))->toBe(38)
        ->and(count(array_unique($kunci)))->toBe(552);
});
