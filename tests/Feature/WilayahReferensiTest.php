<?php

declare(strict_types=1);

use Database\Seeders\WilayahSeeder;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\MuatReferensiWilayah;
use Src\Formasi\Application\PastikanReferensiWilayah;

it('memuat 552 wilayah dengan kode dan provinsi yang benar', function (): void {
    $this->seed(WilayahSeeder::class);

    $sleman = DB::table('wilayahs')->where('kode', '34.04')->first();
    $diy = DB::table('wilayahs')->where('kode', '34')->first();

    expect(jumlah('wilayahs'))->toBe(552)
        ->and(jumlah('wilayahs', ['tingkat' => 'provinsi']))->toBe(38)
        ->and($sleman->nama)->toBe('Kabupaten Sleman')
        ->and($sleman->tingkat)->toBe('kabupaten')
        ->and($sleman->provinsi_id)->toBe($diy->id)
        ->and($diy->provinsi_id)->toBe($diy->id)
        ->and(DB::table('wilayahs')->whereNull('provinsi_id')->count())->toBe(0);
});

it('idempoten: memuat dua kali tidak menggandakan wilayah', function (): void {
    app(MuatReferensiWilayah::class)->handle();
    $idPertama = DB::table('wilayahs')->where('kode', '34.04')->value('id');
    app(MuatReferensiWilayah::class)->handle();

    expect(jumlah('wilayahs'))->toBe(552)->and(DB::table('wilayahs')->where('kode', '34.04')->value('id'))->toBe($idPertama);
});

it('PastikanReferensiWilayah hanya memuat bila belum ada', function (): void {
    app(PastikanReferensiWilayah::class)->handle();
    DB::table('wilayahs')->where('kode', '11')->delete();
    DB::table('wilayahs')->where('provinsi_id', '!=', 0)->where('kode', 'like', '11.%')->delete();
    $sisa = jumlah('wilayahs');

    app(PastikanReferensiWilayah::class)->handle();

    expect(jumlah('wilayahs'))->toBe($sisa);
});

it('memetakan ulang formasi lama yang belum punya wilayah setelah referensi dimuat', function (): void {
    fakeBkn(barisBkn());
    DB::table('wilayahs')->delete();
    sinkron();
    DB::table('formasis')->update(['wilayah_id' => null, 'provinsi_id' => null]);

    $hasil = app(MuatReferensiWilayah::class)->handle();

    $malang = DB::table('formasis')->where('lokasi_teks', 'Kota Malang')->first();
    $jateng = DB::table('wilayahs')->where('nama', 'Jawa Tengah')->value('id');
    $provJatim = DB::table('wilayahs')->where('kode', '35')->value('id');

    expect($hasil->formasiDipetakan)->toBeGreaterThan(0)
        ->and($malang->provinsi_id)->toBe($provJatim)
        ->and(DB::table('formasis')->where('lokasi_teks', 'Prov. Jawa Tengah')->value('provinsi_id'))->toBe($jateng)
        ->and(DB::table('formasis')->where('tipe_lokasi', 'satuan_kerja')->whereNotNull('wilayah_id')->count())->toBe(0);
});

it('mengisi provinsi formasi saat sinkron dan membiarkan satuan kerja tanpa provinsi', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    $sleman = DB::table('formasis')->where('lokasi_teks', 'Kab. Sleman')->first();

    expect($sleman->provinsi_id)->toBe(DB::table('wilayahs')->where('kode', '34')->value('id'))
        ->and(DB::table('formasis')->where('tipe_lokasi', 'satuan_kerja')->value('provinsi_id'))->toBeNull();
});

it('tidak membuat wilayah baru dari lokasi yang tidak dikenal', function (): void {
    $baris = barisCpns();
    $baris[0]['lokasi_formasi'] = 'Kab. Atlantis';
    fakeBkn($baris);
    sinkron();

    expect(jumlah('wilayahs'))->toBe(552)
        ->and(DB::table('formasis')->where('lokasi_teks', 'Kab. Atlantis')->value('wilayah_id'))->toBeNull();
});

it('perintah artisan memuat wilayah', function (): void {
    $this->artisan('sicasn:muat-wilayah')->expectsOutputToContain('552 wilayah dimuat')->assertSuccessful();
});
