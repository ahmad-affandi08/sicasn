<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Src\DataImport\Application\AmbilEventBaru;
use Src\DataImport\Application\TandaiEventDiberitahukan;

it('mengabaikan seluruh baris PPPK dari fixture bentuk asli BKN dan tetap mencatat eventnya', function (): void {
    $contoh = json_decode((string) file_get_contents(base_path('docs/samples/bkn-spf-sample.json')), true)['data'];
    fakeBkn($contoh);

    $hasil = sinkron();

    expect($hasil->berhasil)->toBeTrue()
        ->and($hasil->ringkasan['diabaikan'])->toBe(2)
        ->and($hasil->ringkasan['baris_aktif'])->toBe(0)
        ->and($hasil->ringkasan['event_baru'])->toBe(0)
        ->and(jumlah('formasis'))->toBe(0)
        ->and(jumlah('event_terdeteksis', ['diabaikan' => true]))->toBe(2)
        ->and(DB::table('import_batches')->value('status'))->toBe('sukses');
});

it('menangani respons tanpa satu pun formasi CPNS dengan mulus', function (): void {
    fakeBkn([]);

    $hasil = sinkron();

    expect($hasil->berhasil)->toBeTrue()
        ->and($hasil->ringkasan['total_baris'])->toBe(0)
        ->and($hasil->ringkasan['penarikan_ditunda'])->toBeFalse();
});

it('memproses baris CPNS ke katalog dengan referensi dan kualifikasi ternormalisasi', function (): void {
    fakeBkn(barisBkn());

    $hasil = sinkron();

    expect($hasil->berhasil)->toBeTrue()
        ->and($hasil->ringkasan)->toMatchArray(['total_baris' => 6, 'baris_aktif' => 5, 'diabaikan' => 1, 'baru' => 5, 'diubah' => 0, 'ditarik' => 0, 'gagal_baris' => 0]);

    expect(jumlah('formasis'))->toBe(5)
        ->and(jumlah('formasis', ['jenis_pengadaan' => 'cpns']))->toBe(5)
        ->and(jumlah('instansis'))->toBe(4)
        ->and(jumlah('jabatans'))->toBe(4)
        ->and(jumlah('wilayahs'))->toBe(3)
        ->and(jumlah('kualifikasi_sets'))->toBe(3)
        ->and(jumlah('kualifikasi_set_items'))->toBe(6)
        ->and(jumlah('persyaratans'))->toBe(3)
        ->and(jumlah('formasi_persyaratans'))->toBe(3 + 3 + 2 + 2 + 2);
});

it('mengklasifikasi instansi dan lokasi dengan benar', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    $tipe = DB::table('instansis')->pluck('tipe', 'nama')->all();
    $wilayah = DB::table('wilayahs')->pluck('tingkat', 'nama')->all();

    expect($tipe)->toMatchArray([
        'Kementerian Agama' => 'pusat',
        'Pemerintah Kabupaten Sleman' => 'daerah',
        'Pemerintah Provinsi Jawa Tengah' => 'daerah',
        'Badan Pusat Statistik' => 'unknown',
    ])->and($wilayah)->toBe(['Sleman' => 'kabupaten', 'Malang' => 'kota', 'Jawa Tengah' => 'provinsi']);

    $satuanKerja = DB::table('formasis')->where('tipe_lokasi', 'satuan_kerja')->first();

    expect($satuanKerja->wilayah_id)->toBeNull()->and($satuanKerja->lokasi_teks)->toContain('Wilayah II');
});

it('menyimpan data formasi dengan benar dan menandai kualifikasi yang tidak tersedia', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    $pranata = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000004')->first();
    $analis = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000003')->first();
    $penghulu = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000001')->first();

    expect($pranata->kualifikasi_tersedia)->toBe(0)
        ->and($pranata->kualifikasi_set_id)->toBeNull()
        ->and($pranata->kuota)->toBe(4)
        ->and($penghulu->kualifikasi_tersedia)->toBe(1)
        ->and($penghulu->kuota)->toBe(3)
        ->and($penghulu->usia_min)->toBe(18)
        ->and($penghulu->usia_max)->toBe(35)
        ->and($penghulu->status)->toBe('aktif')
        ->and($analis->is_disabilitas)->toBe(1)
        ->and($penghulu->is_disabilitas)->toBe(0);
});

it('membagikan satu set kualifikasi untuk formasi dengan teks pendidikan yang sama', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    $a = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000001')->value('kualifikasi_set_id');
    $b = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000002')->value('kualifikasi_set_id');

    $item = DB::table('kualifikasi_set_items')->where('kualifikasi_set_id', $a)->orderBy('id')->get(['jenjang', 'semua_jurusan']);

    expect($a)->toBe($b)->and($item->pluck('jenjang')->all())->toBe(['S1', 'S1', 'D4'])->and($item->pluck('semua_jurusan')->unique()->all())->toBe([0]);
});

it('mencatat event baru CPNS sebagai diproses dan event PPPK sebagai diabaikan', function (): void {
    fakeBkn(barisBkn());

    $hasil = sinkron();

    $event = DB::table('event_terdeteksis')->pluck('diabaikan', 'event_id')->all();

    expect($hasil->ringkasan['event_baru'])->toBe(1)
        ->and($event)->toBe(['evt-cpns-sintetis' => 0, 'evt-pppk-sintetis' => 1]);

    majukanWaktu();
    fakeBkn(barisBkn());
    expect(sinkron()->ringkasan)->toHaveKey('dilewati');
});

it('hanya memberi tahu event CPNS yang belum diberitahukan', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    $baru = app(AmbilEventBaru::class)->handle();

    expect($baru)->toHaveCount(1)->and($baru[0]->namaEvent)->toContain('CPNS');

    app(TandaiEventDiberitahukan::class)->handle();

    expect(app(AmbilEventBaru::class)->handle())->toBe([]);
});

it('melewati pemrosesan bila respons identik dengan sinkron sebelumnya', function (): void {
    fakeBkn(barisBkn());
    sinkron();
    majukanWaktu();
    fakeBkn(barisBkn());

    $kedua = sinkron();

    expect($kedua->berhasil)->toBeTrue()
        ->and($kedua->ringkasan['dilewati'])->toBeTrue()
        ->and(jumlah('formasis'))->toBe(5)
        ->and(jumlah('import_batches'))->toBe(2);
});

it('idempoten: respons yang berbeda tetapi isi formasi sama tidak mengubah apa pun', function (): void {
    fakeBkn(barisBkn());
    sinkron();
    $sebelum = DB::table('formasis')->orderBy('id')->pluck('hash_baris', 'external_id')->all();

    majukanWaktu();
    $tambahan = [...barisBkn(), [...barisBkn()[5], 'id' => '00000000-0000-4000-8000-000000000099']];
    fakeBkn($tambahan);
    $hasil = sinkron();

    expect($hasil->ringkasan)->toMatchArray(['baru' => 0, 'diubah' => 0, 'tak_berubah' => 5, 'ditarik' => 0])
        ->and(DB::table('formasis')->orderBy('id')->pluck('hash_baris', 'external_id')->all())->toBe($sebelum)
        ->and(jumlah('persyaratans'))->toBe(3)
        ->and(jumlah('formasi_persyaratans'))->toBe(12);
});

it('memperbarui formasi yang berubah dan mengganti persyaratannya', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    majukanWaktu();
    $baris = barisBkn();
    $baris[0]['jumlah'] = 9;
    $baris[0]['persyaratan'] = [['id' => 'x', 'nama' => 'Syarat baru khusus penghulu.', 'mandatory' => 1]];
    fakeBkn($baris);

    $hasil = sinkron();
    $id = DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000001')->value('id');
    $syarat = DB::table('formasi_persyaratans')->join('persyaratans', 'persyaratans.id', '=', 'formasi_persyaratans.persyaratan_id')->where('formasi_id', $id)->pluck('uraian')->all();

    expect($hasil->ringkasan)->toMatchArray(['baru' => 0, 'diubah' => 1, 'tak_berubah' => 4])
        ->and(DB::table('formasis')->where('id', $id)->value('kuota'))->toBe(9)
        ->and($syarat)->toBe(['Syarat baru khusus penghulu.']);
});

it('memproses lebih dari satu potongan data', function (): void {
    $contoh = barisBkn()[0];
    $banyak = array_map(fn (int $i) => [...$contoh, 'id' => sprintf('00000000-0000-4000-9000-%012d', $i), 'lokasi_formasi' => 'Kab. Contoh '.($i % 7)], range(1, 1250));
    fakeBkn($banyak);

    $hasil = sinkron();

    expect($hasil->ringkasan['baru'])->toBe(1250)
        ->and(jumlah('formasis'))->toBe(1250)
        ->and(jumlah('wilayahs'))->toBe(7)
        ->and(jumlah('instansis'))->toBe(1);
});

it('menyatukan baris dengan id ganda dalam satu respons', function (): void {
    $baris = barisCpns();
    $baris[] = [...$baris[0], 'jumlah' => 77];
    fakeBkn($baris);

    sinkron();

    expect(jumlah('formasis'))->toBe(5)
        ->and(DB::table('formasis')->where('external_id', '00000000-0000-4000-8000-000000000001')->value('kuota'))->toBe(77);
});

it('memproses jenis pengadaan lain bila kebijakan mengaktifkannya', function (): void {
    config(['sicasn.jenis_pengadaan_aktif' => ['cpns', 'pppk_guru']]);
    fakeBkn(barisBkn());

    $hasil = sinkron();

    expect($hasil->ringkasan['baris_aktif'])->toBe(6)->and(jumlah('formasis', ['jenis_pengadaan' => 'pppk_guru']))->toBe(1);
});

it('mengirim agen pengguna yang jujur beserta kontak dari pengaturan email', function (): void {
    konfigurasiEmailTersimpan();
    fakeBkn(barisBkn());

    sinkron();

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->header('User-Agent')[0], 'SICASN/1.0 (+mailto:noreply@contoh.id)')
        && $permintaan->header('Accept')[0] === 'application/json');
});

it('tidak menyisakan berkas sementara setelah sinkron berhasil maupun gagal', function (): void {
    $awal = berkasSementara();

    fakeBkn(barisBkn());
    sinkron();
    fakeBknMentah('<html>', 403);
    majukanWaktu();
    sinkron();

    expect(berkasSementara())->toBe($awal);
});
