<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\DataImport\Application\RollbackBatchStatistik;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
    siapkanFormasi();
    siapkanStatistikFormasi();
});

it('menyimpan statistik jabatan dengan nama ternormalisasi dan wilayah yang terpetakan', function (): void {
    siapkanStatistikJabatan();

    $baris = DB::table('statistik_pelamars')->where('cakupan', 'jabatan')->where('nama_jabatan', 'Guru Madya')->first();

    expect(jumlah('statistik_pelamars', ['cakupan' => 'jabatan']))->toBe(3)
        ->and($baris->jabatan_normal)->toBe('GURU MADYA')
        ->and($baris->wilayah_id)->toBe(DB::table('wilayahs')->where('kode', '35.78')->value('id'))
        ->and($baris->tingkat_data)->toBe('B')
        ->and(DB::table('statistik_pelamars')->where('nama_jabatan', 'like', 'penghulu%')->value('jabatan_normal'))->toBe('PENGHULU AHLI PERTAMA');
});

it('menampilkan peluang per jabatan di daftar formasi dengan label lolos administrasi', function (): void {
    siapkanStatistikJabatan();

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.peluangJabatan.lolosAdministrasi', 600)
        ->where('item.0.peluangJabatan.formasi', 30)
        ->where('item.0.peluangJabatan.rasio', 20)
        ->where('item.0.peluangJabatan.tingkat', 'sedang')
        ->where('item.0.peluangJabatan.batasBawah', true)
        ->where('item.0.peluangJabatan.tingkatData', 'B')
        ->where('item.0.peluangJabatan.keyakinan', 'turunan')
        ->where('item.0.peluangJabatan.sumberNama', 'Pengumuman Uji B')
        ->where('item.0.peluang.tingkat', 'ketat'));
});

it('tidak mencocokkan jabatan bila lokasinya berbeda dan tetap memakai rata-rata instansi', function (): void {
    siapkanStatistikJabatan();

    $this->get('/formasi?cari=Guru Madya')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.peluangJabatan', null)
        ->where('item.0.peluang.tingkat', 'ketat'));
});

it('mencocokkan jabatan pada lokasi tingkat provinsi lewat wilayah yang sama', function (): void {
    siapkanStatistikJabatan();

    $this->get('/formasi?cari=Analis Kebijakan')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.peluangJabatan.lolosAdministrasi', 50)
        ->where('item.0.peluangJabatan.rasio', null)
        ->where('item.0.peluangJabatan.tingkat', 'belum_ada_data')
        ->where('item.0.peluangJabatan.lokasi', 'Prov. Jawa Tengah'));
});

it('memberi null pada formasi yang jabatannya tidak punya data', function (): void {
    siapkanStatistikJabatan();

    $this->get('/formasi?cari=Perawat')->assertInertia(fn (Assert $h) => $h->where('item.0.peluangJabatan', null));
});

it('tidak mencampur data jabatan dari instansi lain yang namanya sama', function (): void {
    muatStatistik([['cakupan' => 'jabatan', 'instansi' => 'Badan Pusat Statistik', 'nama_jabatan' => 'Penghulu Ahli Pertama', 'jumlah_lolos_administrasi' => '9', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'B']]);

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h->where('item.0.peluangJabatan', null));
});

it('menampilkan detail dengan peluang dan riwayat jabatan', function (): void {
    siapkanStatistikJabatan();
    muatStatistik([['cakupan' => 'jabatan', 'instansi' => 'Kementerian Agama', 'nama_jabatan' => 'Penghulu Ahli Pertama', 'jumlah_pelamar' => '900', 'jumlah_formasi' => '30', 'tanggal_snapshot' => '2024-09-10', 'status_angka' => 'sementara', 'sumber_nama' => 'Rekap Awal']]);

    $this->get('/formasi/'.idFormasi('Penghulu Ahli Pertama'))->assertInertia(fn (Assert $h) => $h
        ->where('peluangJabatan.tingkatData', 'B')
        ->where('peluangJabatan.lolosAdministrasi', 600)
        ->has('riwayatJabatan', 2)
        ->where('riwayatJabatan.1.sumberNama', 'Rekap Awal')
        ->where('peluang.tingkat', 'ketat'));
});

it('detail tanpa data jabatan mengembalikan null dan riwayat kosong', function (): void {
    $this->get('/formasi/'.idFormasi('Pengelola Umum'))->assertInertia(fn (Assert $h) => $h->where('peluangJabatan', null)->where('riwayatJabatan', []));
});

it('data tingkat C menggantikan tingkat B untuk jabatan dan lokasi yang sama', function (): void {
    siapkanStatistikJabatan();
    muatStatistik([['cakupan' => 'jabatan', 'instansi' => 'Kementerian Agama', 'nama_jabatan' => 'Penghulu Ahli Pertama', 'jumlah_pelamar' => '1500', 'jumlah_formasi' => '30', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'C', 'keyakinan' => 'resmi', 'sumber_nama' => 'PPID BKN']]);

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.peluangJabatan.tingkatData', 'C')
        ->where('item.0.peluangJabatan.keyakinan', 'resmi')
        ->where('item.0.peluangJabatan.rasio', 50)
        ->where('item.0.peluangJabatan.tingkat', 'ketat')
        ->where('item.0.peluangJabatan.batasBawah', false));
});

it('rollback menghapus data jabatan dan kembali ke rata-rata instansi', function (): void {
    $batch = muatStatistik([['cakupan' => 'jabatan', 'instansi' => 'Kementerian Agama', 'nama_jabatan' => 'Penghulu Ahli Pertama', 'jumlah_lolos_administrasi' => '600', 'jumlah_formasi' => '30', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'B']]);

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h->where('item.0.peluangJabatan.lolosAdministrasi', 600));

    app(RollbackBatchStatistik::class)->handle($batch, $this->member->id);

    $this->get('/formasi?cari=penghulu')->assertInertia(fn (Assert $h) => $h->where('item.0.peluangJabatan', null)->where('item.0.peluang.tingkat', 'ketat'));
});

it('data jabatan tidak mengubah peringkat dan angka instansi', function (): void {
    $sebelum = $this->get('/peluang-instansi')->viewData('page')['props']['paginasi']['total'];

    siapkanStatistikJabatan();

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', $sebelum));
    expect(jumlah('peluang_instansis'))->toBe($sebelum);
});

it('perintah agregat menghasilkan CSV yang bisa diimpor tanpa memuat nama peserta', function (): void {
    $dir = sys_get_temp_dir();
    $masuk = $dir.'/lampiran-uji-'.uniqid().'.txt';
    $keluar = $dir.'/agregat-uji-'.uniqid().'.csv';
    file_put_contents($masuk, "1  REG001  PESERTA UJI 1  Penghulu Ahli Pertama  Kab. Sleman  MS\n2  REG002  PESERTA UJI 2  Penghulu Ahli Pertama  Kab. Sleman  MS\nbaris lain\n");

    $this->artisan('sicasn:agregat-lampiran', [
        'berkas' => $masuk,
        '--pola' => '/^\s*\d+\s+REG\d+\s+PESERTA UJI \d+\s{2,}(?<jabatan>.+?)\s{2,}(?<lokasi>Kab\.\s.+?)\s+MS\s*$/u',
        '--instansi' => 'Kementerian Agama',
        '--tanggal' => '2024-09-19',
        '--sumber' => 'Pengumuman Uji',
        '--total-resmi' => '2',
        '--keluaran' => $keluar,
    ])->expectsOutputToContain('2 peserta dihitung pada 1 jabatan')->assertSuccessful();

    $isi = (string) file_get_contents($keluar);

    expect($isi)->toContain('Penghulu Ahli Pertama')->not->toContain('PESERTA UJI')->not->toContain('REG00');

    unlink($masuk);
    unlink($keluar);
});

it('perintah agregat gagal bila total resmi tidak cocok dan tidak menulis berkas', function (): void {
    $masuk = sys_get_temp_dir().'/lampiran-uji-'.uniqid().'.txt';
    $keluar = sys_get_temp_dir().'/agregat-uji-'.uniqid().'.csv';
    file_put_contents($masuk, "1  A  Perawat\n");

    $this->artisan('sicasn:agregat-lampiran', ['berkas' => $masuk, '--pola' => '/^1\s+A\s+(?<jabatan>.+)$/', '--instansi' => 'X', '--tanggal' => '2024-09-19', '--sumber' => 'S', '--total-resmi' => '9', '--keluaran' => $keluar])
        ->expectsOutputToContain('tidak sama dengan total resmi')->assertFailed();

    expect(file_exists($keluar))->toBeFalse();

    unlink($masuk);
});
