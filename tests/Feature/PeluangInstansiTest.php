<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\SimpanPengaturanPersaingan;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
});

function dataPeluang(): void
{
    muatStatistik([
        ['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500', 'tanggal_snapshot' => '2024-09-17'],
        ['instansi' => 'Badan Riset dan Inovasi Nasional', 'jumlah_formasi' => '500', 'jumlah_pelamar' => '485', 'tanggal_snapshot' => '2024-09-17'],
        ['instansi' => 'Pemerintah Kabupaten Bangli', 'jumlah_formasi' => '9', 'jumlah_submit' => '5', 'tanggal_snapshot' => '2024-08-29', 'status_angka' => 'sementara'],
        ['instansi' => 'Kementerian Hukum dan HAM', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '9000', 'tanggal_snapshot' => '2024-09-17'],
        ['instansi' => 'Kementerian Hukum dan HAM', 'jumlah_pelamar' => '5000', 'tanggal_snapshot' => '2024-08-29', 'status_angka' => 'sementara', 'sumber_nama' => 'Sumber Awal'],
        ['instansi' => 'Pemerintah Provinsi Jawa Tengah', 'jumlah_formasi' => '', 'jumlah_pelamar' => '700', 'tanggal_snapshot' => '2024-09-17'],
    ]);
}

it('hanya bisa dibuka setelah login', function (): void {
    $this->post('/keluar');
    auth()->forgetGuards();
    $this->withCookie(config('session.cookie'), '')->get('/peluang-instansi')->assertRedirect('/masuk');
});

it('menampilkan keadaan kosong bila statistik belum dimuat', function (): void {
    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h
        ->component('PeluangInstansi')
        ->where('totalInstansi', 0)
        ->where('item', [])
        ->where('nasional', []));
});

it('menampilkan instansi beserta rasio, tingkat, dan sumbernya', function (): void {
    dataPeluang();

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h
        ->where('totalInstansi', 5)
        ->where('paginasi.total', 5)
        ->has('item', 5)
        ->where('item.0.nama', 'Badan Riset dan Inovasi Nasional')
        ->where('item.0.tingkat', 'longgar')
        ->where('item.0.rasio', 1)
        ->where('item.0.terbaru.sumberNama', 'Sumber Uji')
        ->where('item.0.terbaru.tingkatData', 'A'));
});

it('memakai angka akhir terbaru dan menyimpan snapshot lama sebagai riwayat', function (): void {
    dataPeluang();

    $this->get('/peluang-instansi?cari=Hukum')->assertInertia(fn (Assert $h) => $h
        ->has('item', 1)
        ->where('item.0.pelamar', 9000)
        ->where('item.0.tingkat', 'ketat')
        ->where('item.0.rasio', 90)
        ->where('item.0.batasBawah', false)
        ->has('item.0.riwayat', 2)
        ->where('item.0.riwayat.1.sumberNama', 'Sumber Awal')
        ->where('item.0.riwayat.1.status', 'sementara'));
});

it('menandai angka sementara sebagai belum final dan batas bawah', function (): void {
    dataPeluang();

    $this->get('/peluang-instansi?cari=Bangli')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.tingkat', 'belum_final')
        ->where('item.0.batasBawah', true)
        ->where('item.0.pelamar', null)
        ->where('item.0.submit', 5));
});

it('meminjam jumlah formasi dari snapshot lain dan mencantumkan tanggalnya', function (): void {
    muatStatistik([
        ['instansi' => 'Kementerian Uji', 'jumlah_formasi' => '100', 'tanggal_snapshot' => '2024-09-11'],
        ['instansi' => 'Kementerian Uji', 'jumlah_pelamar' => '1000', 'tanggal_snapshot' => '2024-09-17'],
    ]);

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.formasi', 100)
        ->where('item.0.formasiDariTanggal', '2024-09-11')
        ->where('item.0.rasio', 10)
        ->where('item.0.tingkat', 'sedang'));
});

it('menyatakan belum ada data bila tidak ada formasi sama sekali', function (): void {
    dataPeluang();

    $this->get('/peluang-instansi?cari=Jawa')->assertInertia(fn (Assert $h) => $h
        ->where('item.0.tingkat', 'belum_ada_data')
        ->where('item.0.rasio', null));
});

it('menyaring berdasarkan nama, jenis instansi, dan tingkat persaingan', function (): void {
    dataPeluang();

    $this->get('/peluang-instansi?cari=kementerian')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', 2));
    $this->get('/peluang-instansi?tipe=daerah')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', 2));
    $this->get('/peluang-instansi?tipe=pusat')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', 2));
    $this->get('/peluang-instansi?tingkat=ketat')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', 2)->where('filter.tingkat', 'ketat'));
    $this->get('/peluang-instansi?tingkat=belum_final&tipe=daerah')->assertInertia(fn (Assert $h) => $h->where('paginasi.total', 1));
});

it('mengurutkan sesuai pilihan', function (): void {
    dataPeluang();

    $nama = fn (string $urut) => collect($this->get("/peluang-instansi?urut={$urut}")->viewData('page')['props']['item'])->pluck('nama')->all();

    expect($nama('nama')[0])->toBe('Badan Riset dan Inovasi Nasional')
        ->and($nama('formasi_terbanyak')[0])->toBe('Badan Riset dan Inovasi Nasional')
        ->and($nama('rasio_terendah')[0])->toBe('Badan Riset dan Inovasi Nasional');
});

it('mengarahkan ulang ke halaman bersih bila parameter tidak valid', function (): void {
    foreach (['tingkat=asal', 'tipe=asal', 'urut=asal', 'halaman=0', 'halaman=abc'] as $kueri) {
        $this->get("/peluang-instansi?{$kueri}")->assertRedirect('/peluang-instansi');
    }
});

it('membagi hasil per halaman', function (): void {
    muatStatistik(array_map(fn (int $i) => ['instansi' => sprintf('Instansi Uji %02d', $i), 'jumlah_formasi' => '10', 'jumlah_pelamar' => (string) (100 + $i)], range(1, 30)));

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h
        ->has('item', 25)
        ->where('paginasi.total', 30)
        ->where('paginasi.terakhir', 2));

    $this->get('/peluang-instansi?halaman=2')->assertInertia(fn (Assert $h) => $h
        ->has('item', 5)
        ->where('paginasi.halaman', 2));
});

it('menyertakan angka nasional dengan sumber dan tingkat keyakinannya', function (): void {
    muatStatistik([
        ['cakupan' => 'nasional', 'jumlah_pelamar' => '3963832', 'keyakinan' => 'resmi', 'sumber_nama' => 'BKN', 'sumber_url' => 'https://contoh.id/bkn'],
        ['cakupan' => 'nasional', 'jumlah_formasi' => '250407', 'tanggal_snapshot' => '2024-10-16', 'keyakinan' => 'resmi', 'sumber_nama' => 'BKN Rilis'],
    ]);

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $h) => $h
        ->has('nasional', 2)
        ->where('nasional.0.jenis', 'pelamar')
        ->where('nasional.0.nilai', 3963832)
        ->where('nasional.0.sumberUrl', 'https://contoh.id/bkn')
        ->where('nasional.0.keyakinan', 'resmi')
        ->where('nasional.1.jenis', 'formasi'));
});

it('memakai ambang persaingan yang diatur admin', function (): void {
    dataPeluang();
    app(SimpanPengaturanPersaingan::class)->handle(1.0, 2.0, $this->member->id);

    $this->get('/peluang-instansi?cari=Agama')->assertInertia(fn (Assert $h) => $h
        ->where('ambang.longgarMaks', 1)
        ->where('ambang.ketatMin', 2)
        ->where('item.0.tingkat', 'ketat'));
});

it('tidak membocorkan data pengguna lain pada props', function (): void {
    dataPeluang();
    $lain = UserModel::factory()->create();

    $props = json_encode($this->get('/peluang-instansi')->viewData('page')['props']);

    expect($props)->not->toContain('password')->not->toContain($lain->email);
});

it('menaruh angka sementara di bawah angka akhir saat diurutkan menurut rasio atau pelamar', function (): void {
    dataPeluang();

    $nama = fn (string $urut) => collect($this->get("/peluang-instansi?urut={$urut}")->viewData('page')['props']['item'])->pluck('nama')->all();

    expect($nama('rasio_terendah'))->toBe(['Badan Riset dan Inovasi Nasional', 'Kementerian Agama', 'Kementerian Hukum dan HAM', 'Pemerintah Kabupaten Bangli', 'Pemerintah Provinsi Jawa Tengah'])
        ->and($nama('pelamar_tersedikit'))->toBe(['Badan Riset dan Inovasi Nasional', 'Pemerintah Provinsi Jawa Tengah', 'Kementerian Agama', 'Kementerian Hukum dan HAM', 'Pemerintah Kabupaten Bangli']);
});

it('dapat dibuka admin juga', function (): void {
    $this->actingAs(UserModel::factory()->admin()->create())->get('/peluang-instansi')->assertOk();
});
