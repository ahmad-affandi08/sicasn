<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\DataImport\Application\RollbackBatchStatistik;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\SimpanPengaturanPersaingan;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
});

function ambilJson(string $url)
{
    return test()->get($url, ['Accept' => 'application/json']);
}

function jabatanHalaman(string $kueri = ''): array
{
    return collect(test()->get('/formasi'.($kueri === '' ? '' : '?'.$kueri))->viewData('page')['props']['item'])->pluck('jabatan')->all();
}

it('hanya bisa dibuka setelah login', function (): void {
    $this->post('/keluar');
    auth()->forgetGuards();

    $this->withCookie(config('session.cookie'), '')->get('/formasi')->assertRedirect('/masuk');
});

it('menampilkan keadaan kosong bila belum ada formasi CPNS aktif', function (): void {
    $this->get('/formasi')->assertInertia(fn (Assert $h) => $h
        ->component('Formasi/Index')
        ->where('totalAktif', 0)
        ->where('item', [])
        ->where('paginasi.total', 0));
});

it('hanya menampilkan formasi CPNS yang aktif', function (): void {
    siapkanFormasi();
    DB::table('formasis')->where('id', idFormasi('Guru Madya'))->update(['status' => 'ditarik']);

    $this->get('/formasi')->assertInertia(fn (Assert $h) => $h->where('totalAktif', 5)->where('paginasi.total', 5));

    expect(jabatanHalaman())->not->toContain('Guru Madya');
});

it('mencari pada nama jabatan atau instansi tanpa membedakan huruf besar kecil', function (): void {
    siapkanFormasi();

    expect(jabatanHalaman('cari=penghulu'))->toBe(['Penghulu Ahli Pertama'])
        ->and(jabatanHalaman('cari=pusat statistik'))->toBe(['Statistisi Ahli Pertama'])
        ->and(jabatanHalaman('cari=kementerian agama'))->toHaveCount(3)
        ->and(jabatanHalaman('cari=tidakada'))->toBe([]);
});

it('memperlakukan karakter khusus pencarian sebagai teks biasa', function (): void {
    siapkanFormasi();

    expect(jabatanHalaman('cari=%25'))->toBe([])->and(jabatanHalaman('cari=_'))->toBe([]);
});

it('menyaring jenjang termasuk yang membuka semua jurusan dan mengecualikan kualifikasi kosong', function (): void {
    siapkanFormasi();

    expect(jabatanHalaman('jenjang[]=S1'))->toEqualCanonicalizing(['Penghulu Ahli Pertama', 'Guru Madya', 'Analis Kebijakan', 'Statistisi Ahli Pertama'])
        ->and(jabatanHalaman('jenjang[]=D3'))->toBe(['Perawat Terampil'])
        ->and(jabatanHalaman('jenjang[]=D3&jenjang[]=D4'))->toEqualCanonicalizing(['Perawat Terampil', 'Penghulu Ahli Pertama', 'Analis Kebijakan']);
});

it('menyertakan formasi tanpa rincian kualifikasi hanya bila diminta', function (): void {
    siapkanFormasi();

    expect(jabatanHalaman('jenjang[]=D3'))->not->toContain('Pengelola Umum')
        ->and(jabatanHalaman('jenjang[]=D3&sertakan=1'))->toEqualCanonicalizing(['Perawat Terampil', 'Pengelola Umum']);
});

it('menyaring jurusan dan mencocokkan jenjang dan jurusan pada butir kualifikasi yang sama', function (): void {
    siapkanFormasi();
    $hukum = DB::table('jurusans')->where('nama', 'HUKUM')->value('id');

    expect(jabatanHalaman("jurusan[]={$hukum}"))->toEqualCanonicalizing(['Penghulu Ahli Pertama', 'Analis Kebijakan'])
        ->and(jabatanHalaman("jurusan[]={$hukum}&jenjang[]=S2"))->toBe([])
        ->and(jabatanHalaman("jurusan[]={$hukum}&jenjang[]=D3"))->toBe([]);
});

it('menyaring provinsi dan kabupaten atau kota secara bertingkat', function (): void {
    siapkanFormasi();
    $diy = DB::table('wilayahs')->where('kode', '34')->value('id');
    $jateng = DB::table('wilayahs')->where('kode', '33')->value('id');
    $sleman = DB::table('wilayahs')->where('kode', '34.04')->value('id');

    expect(jabatanHalaman("provinsi={$diy}"))->toEqualCanonicalizing(['Penghulu Ahli Pertama', 'Pengelola Umum', 'Perawat Terampil'])
        ->and(jabatanHalaman("provinsi={$jateng}"))->toEqualCanonicalizing(['Analis Kebijakan', 'Statistisi Ahli Pertama'])
        ->and(jabatanHalaman("provinsi={$diy}&wilayah={$sleman}"))->toHaveCount(3)
        ->and(jabatanHalaman("provinsi={$jateng}&wilayah={$sleman}"))->toBe([]);
});

it('menyaring instansi dan jenis formasi', function (): void {
    siapkanFormasi();
    $agama = DB::table('instansis')->where('nama', 'Kementerian Agama')->value('id');

    expect(jabatanHalaman("instansi={$agama}"))->toHaveCount(3)
        ->and(jabatanHalaman('jenis=DISABILITAS'))->toBe(['Analis Kebijakan']);
});

it('mengurutkan dari rasio terendah dengan data tanpa statistik di akhir', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();

    $urut = jabatanHalaman();

    expect($urut[0])->toBe('Analis Kebijakan')
        ->and(array_slice($urut, 1, 3))->toEqualCanonicalizing(['Penghulu Ahli Pertama', 'Guru Madya', 'Pengelola Umum'])
        ->and(array_slice($urut, 4))->toEqualCanonicalizing(['Statistisi Ahli Pertama', 'Perawat Terampil']);
});

it('mengurutkan menurut kuota, terbaru, dan pelamar tersedikit', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();
    DB::table('formasis')->where('id', idFormasi('Perawat Terampil'))->update(['first_seen_at' => now()->addDay()]);

    expect(jabatanHalaman('urut=kuota_terbanyak')[0])->toBe('Analis Kebijakan')
        ->and(jabatanHalaman('urut=terbaru')[0])->toBe('Perawat Terampil')
        ->and(array_slice(jabatanHalaman('urut=pelamar_tersedikit'), 0, 1))->toBe(['Analis Kebijakan']);
});

it('menyaring tingkat persaingan dari statistik instansi', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();

    expect(jabatanHalaman('tingkat=ketat'))->toEqualCanonicalizing(['Penghulu Ahli Pertama', 'Guru Madya', 'Pengelola Umum'])
        ->and(jabatanHalaman('tingkat=longgar'))->toBe(['Analis Kebijakan'])
        ->and(jabatanHalaman('tingkat=sedang'))->toBe([])
        ->and(jabatanHalaman('tingkat=belum_ada_data'))->toEqualCanonicalizing(['Statistisi Ahli Pertama', 'Perawat Terampil']);
});

it('menyertakan peluang instansi 2024 pada tiap formasi dengan label bukan per jabatan', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.peluang.tingkat', 'ketat')
        ->where('item.0.peluang.rasio', 25)
        ->where('item.0.peluang.pelamar', 2500)
        ->where('item.0.peluang.formasi', 100)
        ->where('item.0.peluang.tingkatData', 'A')
        ->where('item.0.peluang.perJabatan', false)
        ->where('item.0.peluang.sumberNama', 'Sumber Uji')
        ->where('item.0.lokasi', 'Kabupaten Sleman'));

    $this->get('/formasi?cari=statistisi')->assertInertia(fn (Assert $h) => $h->where('item.0.peluang', null));
});

it('mengikuti ambang persaingan yang diatur admin', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();
    app(SimpanPengaturanPersaingan::class)->handle(30.0, 60.0, $this->member->id);

    expect(jabatanHalaman('tingkat=longgar'))->toEqualCanonicalizing(['Analis Kebijakan', 'Penghulu Ahli Pertama', 'Guru Madya', 'Pengelola Umum']);
});

it('membagi hasil menjadi halaman berisi 20 formasi', function (): void {
    fakeBkn(array_map(fn (int $i) => barisFormasi("Jabatan Uji {$i}", ['jumlah' => $i]), range(1, 45)));
    sinkron();

    $this->get('/formasi?urut=kuota_terbanyak')->assertInertia(fn (Assert $h) => $h
        ->has('item', 20)
        ->where('item.0.kuota', 45)
        ->where('paginasi.total', 45)
        ->where('paginasi.terakhir', 3));
    $this->get('/formasi?urut=kuota_terbanyak&halaman=3')->assertInertia(fn (Assert $h) => $h->has('item', 5)->where('item.0.kuota', 5)->where('paginasi.halaman', 3));
    $this->get('/formasi?urut=kuota_terbanyak&halaman=99')->assertInertia(fn (Assert $h) => $h->where('paginasi.halaman', 3)->has('item', 5));
});

it('membatasi paginasi sampai halaman 100 dan memberi tanda bila hasil lebih banyak', function (): void {
    fakeBkn(array_map(fn (int $i) => barisFormasi("Jabatan Banyak {$i}", ['jumlah' => 1 + $i % 50]), range(1, 2030)));
    sinkron();

    $this->get('/formasi?halaman=100')->assertInertia(fn (Assert $h) => $h->where('paginasi.terakhir', 100)->where('paginasi.dibatasi', true)->where('paginasi.total', 2030)->has('item', 20));
    $this->get('/formasi?halaman=101')->assertRedirect('/formasi');
});

it('mengarahkan ulang ke halaman bersih bila parameter tidak valid', function (string $kueri): void {
    $this->get("/formasi?{$kueri}")->assertRedirect('/formasi');
})->with([
    'tingkat salah' => ['tingkat=asal'],
    'urut salah' => ['urut=asal'],
    'jenjang bukan huruf besar' => ['jenjang[]=s1;drop'],
    'jurusan bukan angka' => ['jurusan[]=abc'],
    'provinsi bukan angka' => ['provinsi=abc'],
    'halaman nol' => ['halaman=0'],
    'sertakan salah' => ['sertakan=ya'],
    'jenjang terlalu banyak' => ['jenjang[]=S1&jenjang[]=S2&jenjang[]=S3&jenjang[]=D1&jenjang[]=D2&jenjang[]=D3&jenjang[]=D4&jenjang[]=SD&jenjang[]=SMP&jenjang[]=SMA&jenjang[]=PROFESI&jenjang[]=LAINNYA&jenjang[]=X'],
]);

it('tahan terhadap injeksi SQL lewat pencarian dan jenis formasi', function (): void {
    siapkanFormasi();

    expect(jabatanHalaman("cari=' OR 1=1 --"))->toBe([])->and(jabatanHalaman("jenis=' OR '1'='1"))->toBe([]);
    expect(jumlah('formasis'))->toBe(6);
});

it('menyediakan opsi filter hanya dari formasi yang aktif', function (): void {
    siapkanFormasi();

    $this->get('/formasi?provinsi='.DB::table('wilayahs')->where('kode', '33')->value('id'))->assertInertia(fn (Assert $h) => $h
        ->where('opsi.jenjang', ['D3', 'D4', 'S1'])
        ->has('opsi.provinsi', 3)
        ->where('opsi.kabupatenKota.0.label', 'Kabupaten Temanggung')
        ->has('opsi.kabupatenKota', 1)
        ->where('opsi.jenisFormasi.0.label', 'DISABILITAS'));
});

it('mengembalikan nama instansi dan jurusan terpilih agar chip bisa ditampilkan setelah muat ulang', function (): void {
    siapkanFormasi();
    $agama = DB::table('instansis')->where('nama', 'Kementerian Agama')->value('id');
    $hukum = DB::table('jurusans')->where('nama', 'HUKUM')->value('id');

    $this->get("/formasi?instansi={$agama}&jurusan[]={$hukum}")->assertInertia(fn (Assert $h) => $h
        ->where('opsi.instansiTerpilih.label', 'Kementerian Agama')
        ->where('opsi.jurusanTerpilih.0.label', 'HUKUM')
        ->where('filter.jurusan', [$hukum])
        ->where('filter.instansi', $agama));
});

it('melayani pencarian opsi instansi, jurusan, dan kabupaten atau kota', function (): void {
    siapkanFormasi();
    DB::table('instansis')->insert(['nama' => 'Instansi Tanpa Formasi', 'nama_normal' => 'INSTANSI TANPA FORMASI', 'tipe' => 'unknown', 'created_at' => now(), 'updated_at' => now()]);

    $instansi = collect(ambilJson('/formasi/opsi/instansi?q=kementerian')->assertOk()->json('opsi'))->pluck('label')->all();
    $jurusan = collect(ambilJson('/formasi/opsi/jurusan?q=hukum')->assertOk()->json('opsi'))->pluck('label')->all();
    $diy = DB::table('wilayahs')->where('kode', '34')->value('id');

    expect($instansi)->toBe(['Kementerian Agama', 'Kementerian Hukum dan HAM'])
        ->and($jurusan)->toBe(['HUKUM'])
        ->and(collect(ambilJson("/formasi/opsi/provinsi/{$diy}/wilayah")->json('opsi'))->pluck('label')->all())->toBe(['Kabupaten Sleman'])
        ->and(collect(ambilJson('/formasi/opsi/instansi?q=Tanpa')->json('opsi')))->toBeEmpty();
    ambilJson('/formasi/opsi/provinsi/abc/wilayah')->assertNotFound();
});

it('membatasi laju pencarian opsi', function (): void {
    foreach (range(1, 120) as $i) {
        ambilJson('/formasi/opsi/instansi?q=a')->assertOk();
    }

    ambilJson('/formasi/opsi/instansi?q=a')->assertStatus(429);
});

it('melarang tamu memanggil endpoint opsi', function (): void {
    $this->post('/keluar');
    auth()->forgetGuards();

    $this->withCookie(config('session.cookie'), '')->get('/formasi/opsi/instansi', ['Accept' => 'application/json'])->assertUnauthorized();
});

it('menampilkan detail formasi lengkap dengan kualifikasi, persyaratan, peluang, dan riwayat', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();
    muatStatistik([['instansi' => 'Kementerian Agama', 'jumlah_pelamar' => '1800', 'tanggal_snapshot' => '2024-08-29', 'status_angka' => 'sementara', 'sumber_nama' => 'Sumber Awal']]);

    $this->get('/formasi/'.idFormasi('Penghulu Ahli Pertama'))->assertInertia(fn (Assert $h) => $h
        ->component('Formasi/Detail')
        ->where('formasi.instansi', 'Kementerian Agama')
        ->where('formasi.jabatan', 'Penghulu Ahli Pertama')
        ->where('formasi.lokasi', 'Kabupaten Sleman')
        ->where('formasi.provinsi', 'Daerah Istimewa Yogyakarta')
        ->where('formasi.kuota', 3)
        ->where('formasi.status', 'aktif')
        ->where('formasi.jenisPengadaan', 'CPNS')
        ->has('formasi.kualifikasiItem', 3)
        ->where('formasi.kualifikasiItem.0.jenjang', 'S1')
        ->where('formasi.kualifikasiItem.0.jurusan', 'HUKUM')
        ->has('formasi.persyaratan', 3)
        ->where('formasi.persyaratan.2.wajib', false)
        ->where('peluang.tingkat', 'ketat')
        ->has('riwayat', 2)
        ->where('tautanResmi', 'https://sscasn.bkn.go.id'));
});

it('menampilkan detail formasi yang sudah ditarik dengan statusnya', function (): void {
    siapkanFormasi();
    DB::table('formasis')->where('id', idFormasi('Guru Madya'))->update(['status' => 'ditarik']);

    $this->get('/formasi/'.idFormasi('Guru Madya'))->assertInertia(fn (Assert $h) => $h->where('formasi.status', 'ditarik')->where('peluang', null)->where('riwayat', []));
});

it('menampilkan kualifikasi kosong apa adanya pada detail', function (): void {
    siapkanFormasi();

    $this->get('/formasi/'.idFormasi('Pengelola Umum'))->assertInertia(fn (Assert $h) => $h
        ->where('formasi.kualifikasiTersedia', false)
        ->where('formasi.kualifikasiItem', [])
        ->where('formasi.kualifikasi', null));
});

it('mengembalikan 404 untuk formasi yang tidak ada dan menolak id bukan angka', function (): void {
    $this->get('/formasi/999999')->assertNotFound();
    $this->get('/formasi/abc')->assertNotFound();
});

it('menampilkan jumlah formasi aktif per instansi di halaman Peluang Instansi', function (): void {
    siapkanFormasi();
    siapkanStatistikFormasi();

    $this->get('/peluang-instansi?cari=Kementerian Agama')->assertInertia(fn (Assert $h) => $h->where('item.0.formasiAktif', 3));
    $this->get('/peluang-instansi?cari=Hukum')->assertInertia(fn (Assert $h) => $h->where('item.0.formasiAktif', 1));
});

it('memperbarui read model peluang saat impor dan rollback', function (): void {
    expect(jumlah('peluang_instansis'))->toBe(0);

    $batch = muatStatistik([['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500']]);

    $baris = DB::table('peluang_instansis')->first();

    expect(jumlah('peluang_instansis'))->toBe(1)
        ->and((float) $baris->rasio)->toBe(25.0)
        ->and($baris->golongan_rasio)->toBe(0)
        ->and($baris->sumber_nama)->toBe('Sumber Uji');

    app(RollbackBatchStatistik::class)->handle($batch, $this->member->id);

    expect(jumlah('peluang_instansi'.'s'))->toBe(0);
});

it('menandai angka sementara sebagai golongan batas bawah di read model', function (): void {
    muatStatistik([['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500', 'status_angka' => 'sementara']]);

    $baris = DB::table('peluang_instansis')->first();

    expect($baris->golongan_rasio)->toBe(1)->and($baris->golongan_pelamar)->toBe(1)->and($baris->pelamar_dasar)->toBe(2500);
});
