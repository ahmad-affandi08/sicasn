<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
    Storage::fake('local');
});

function berkasCsv(string $isi, string $nama = 'statistik.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nama, $isi);
}

function unggah(string $isi, string $nama = 'statistik.csv')
{
    return test()->actingAs(test()->admin)->post('/admin/statistik/pratinjau', ['berkas' => berkasCsv($isi, $nama)]);
}

function tokenDariPratinjau($respons): string
{
    preg_match('#/admin/statistik/pratinjau/([A-Za-z0-9]+)$#', (string) $respons->headers->get('Location'), $cocok);

    return $cocok[1];
}

function dua(): string
{
    return csvStatistik([
        ['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500'],
        ['instansi' => 'Kementerian Hukum dan HAM', 'jumlah_formasi' => '50', 'jumlah_pelamar' => '4000'],
    ]);
}

it('melarang tamu dan member mengakses statistik', function (): void {
    $this->get('/admin/statistik')->assertRedirect('/masuk');

    masukSebagai($this, UserModel::factory()->create());

    $this->get('/admin/statistik')->assertForbidden();
    $this->get('/admin/statistik/template')->assertForbidden();
    $this->post('/admin/statistik/pratinjau', ['berkas' => berkasCsv(dua())])->assertForbidden();
    $this->post('/admin/statistik/pratinjau/'.str_repeat('a', 40).'/konfirmasi')->assertForbidden();
    $this->post('/admin/statistik/1/rollback')->assertForbidden();
    $this->get('/admin/statistik/1/galat')->assertForbidden();
    $this->get('/admin/pengaturan/persaingan')->assertForbidden();
    $this->put('/admin/pengaturan/persaingan', ['longgar_maks' => 5, 'ketat_min' => 20])->assertForbidden();
});

it('menampilkan halaman statistik kosong', function (): void {
    $this->actingAs($this->admin)->get('/admin/statistik')->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Statistik/Index')
        ->where('riwayat', [])
        ->where('totalBaris', 0));
});

it('mengunggah CSV lalu menampilkan pratinjau tanpa menyimpan apa pun', function (): void {
    $respons = unggah(dua());
    $token = tokenDariPratinjau($respons);

    expect(jumlah('statistik_pelamars'))->toBe(0)->and(jumlah('import_batches'))->toBe(0);

    $this->actingAs($this->admin)->get("/admin/statistik/pratinjau/{$token}")->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Statistik/Pratinjau')
        ->where('pratinjau.namaBerkas', 'statistik.csv')
        ->where('pratinjau.totalBaris', 2)
        ->where('pratinjau.barisValid', 2)
        ->where('pratinjau.jumlahGalat', 0)
        ->where('pratinjau.ringkasanSumber.0.sumber', 'Sumber Uji')
        ->where('pratinjau.ringkasanSumber.0.jumlah', 2));
});

it('menyimpan data setelah dikonfirmasi dan mencatat batch serta audit', function (): void {
    $token = tokenDariPratinjau(unggah(dua()));

    $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi")
        ->assertRedirect('/admin/statistik')
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses' && str_contains($d['pesan'], '2 baris baru'));

    expect(jumlah('statistik_pelamars'))->toBe(2)
        ->and(jumlah('instansis'))->toBe(2)
        ->and(jumlah('import_batches', ['tipe' => 'csv_statistik', 'status' => 'sukses']))->toBe(1)
        ->and(jumlah('audit_logs', ['aksi' => 'statistik.diimpor']))->toBe(1);

    $this->actingAs($this->admin)->get('/admin/statistik')->assertInertia(fn (Assert $h) => $h
        ->where('totalBaris', 2)
        ->where('riwayat.0.berkas', 'statistik.csv')
        ->where('riwayat.0.status', 'sukses')
        ->where('riwayat.0.bisaDibatalkan', true)
        ->where('riwayat.0.ringkasan.baris_baru', 2));
});

it('menghapus berkas pratinjau setelah dikonfirmasi sehingga tidak bisa dipakai dua kali', function (): void {
    $token = tokenDariPratinjau(unggah(dua()));

    $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi");
    $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi")
        ->assertRedirect('/admin/statistik')
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan');

    expect(jumlah('statistik_pelamars'))->toBe(2)->and(jumlah('import_batches'))->toBe(1);
});

it('idempoten: mengimpor berkas yang sama dua kali tidak menggandakan baris', function (): void {
    foreach ([1, 2] as $putaran) {
        $token = tokenDariPratinjau(unggah(dua()));
        $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi");
    }

    expect(jumlah('statistik_pelamars'))->toBe(2)->and(jumlah('instansis'))->toBe(2);

    $this->actingAs($this->admin)->get('/admin/statistik')->assertInertia(fn (Assert $h) => $h
        ->where('riwayat.0.ringkasan.baris_baru', 0)
        ->where('riwayat.0.ringkasan.baris_duplikat', 2));
});

it('memakai ulang instansi yang namanya sama walau beda huruf dan spasi', function (): void {
    foreach (['Kementerian Agama', '  KEMENTERIAN   AGAMA '] as $i => $nama) {
        $token = tokenDariPratinjau(unggah(csvStatistik([['instansi' => $nama, 'jumlah_formasi' => '10', 'jumlah_pelamar' => '100', 'tanggal_snapshot' => $i === 0 ? '2024-09-17' : '2024-09-11']])));
        $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi");
    }

    expect(jumlah('instansis'))->toBe(1)->and(jumlah('statistik_pelamars'))->toBe(2);
});

it('mengimpor baris valid, melewati yang bermasalah, dan menyediakan unduhan baris gagal', function (): void {
    $isi = csvStatistik([
        ['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500'],
        ['instansi' => '', 'jumlah_pelamar' => '10'],
        ['instansi' => 'Kementerian Uji', 'jumlah_pelamar' => '=HYPERLINK("http://jahat")'],
    ]);
    $token = tokenDariPratinjau(unggah($isi));

    $this->actingAs($this->admin)->get("/admin/statistik/pratinjau/{$token}")->assertInertia(fn (Assert $h) => $h
        ->where('pratinjau.barisValid', 1)
        ->where('pratinjau.jumlahGalat', 2)
        ->where('pratinjau.galat.0.baris', 3)
        ->where('pratinjau.galat.0.kolom', 'instansi'));

    $this->actingAs($this->admin)->post("/admin/statistik/pratinjau/{$token}/konfirmasi")->assertSessionHas('dialog', fn (array $d) => str_contains($d['pesan'], '2 baris dilewati'));

    $batch = DB::table('import_batches')->first();

    expect($batch->status)->toBe('sebagian')->and(jumlah('statistik_pelamars'))->toBe(1);

    $unduh = $this->actingAs($this->admin)->get("/admin/statistik/{$batch->id}/galat");

    $unduh->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($unduh->getContent())->toContain('baris,kolom,pesan,nilai')->toContain("'=HYPERLINK");
});

it('menolak berkas yang bukan CSV, kosong, atau terlalu besar', function (): void {
    $this->actingAs($this->admin)->post('/admin/statistik/pratinjau', ['berkas' => UploadedFile::fake()->create('data.pdf', 10, 'application/pdf')])->assertSessionHasErrors('berkas');
    $this->actingAs($this->admin)->post('/admin/statistik/pratinjau', ['berkas' => UploadedFile::fake()->create('besar.csv', 3000, 'text/csv')])->assertSessionHasErrors('berkas');
    $this->actingAs($this->admin)->post('/admin/statistik/pratinjau', [])->assertSessionHasErrors('berkas');

    unggah('')->assertRedirect()->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
    unggah("tahun,instansi\n2024,A\n")->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal' && str_contains($d['pesan'], 'Kolom wajib'));

    expect(jumlah('statistik_pelamars'))->toBe(0);
});

it('tidak membuat pratinjau bila tidak ada satu pun baris valid', function (): void {
    unggah(csvStatistik([['instansi' => '', 'jumlah_pelamar' => '10']]))->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
});

it('menolak token pratinjau yang bentuknya tidak wajar atau tidak ada', function (): void {
    $this->actingAs($this->admin)->get('/admin/statistik/pratinjau/..%2F..%2Fetc')->assertNotFound();
    $this->actingAs($this->admin)->get('/admin/statistik/pratinjau/pendek')->assertNotFound();
    $this->actingAs($this->admin)->get('/admin/statistik/pratinjau/'.str_repeat('a', 40))
        ->assertRedirect('/admin/statistik')
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan');
});

it('membatalkan impor dengan rollback hanya pada baris batch itu', function (): void {
    $batchPertama = muatStatistik([['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '10', 'jumlah_pelamar' => '100']]);
    $batchKedua = muatStatistik([['instansi' => 'Kementerian Hukum', 'jumlah_formasi' => '10', 'jumlah_pelamar' => '100']]);

    $this->actingAs($this->admin)->post("/admin/statistik/{$batchKedua}/rollback")
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses' && str_contains($d['pesan'], '1 baris'));

    expect(jumlah('statistik_pelamars'))->toBe(1)
        ->and(DB::table('import_batches')->where('id', $batchKedua)->value('status'))->toBe('dibatalkan')
        ->and(DB::table('import_batches')->where('id', $batchPertama)->value('status'))->toBe('sukses')
        ->and(jumlah('audit_logs', ['aksi' => 'statistik.dibatalkan']))->toBe(1);

    $this->actingAs($this->admin)->get('/admin/statistik')->assertInertia(fn (Assert $h) => $h
        ->where('riwayat.0.status', 'dibatalkan')
        ->where('riwayat.0.bisaDibatalkan', false)
        ->where('riwayat.0.ringkasan.baris_dihapus', 1));
});

it('menolak rollback ganda, batch yang tidak ada, dan batch sinkron BKN', function (): void {
    $batch = muatStatistik([['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '10', 'jumlah_pelamar' => '100']]);
    $this->actingAs($this->admin)->post("/admin/statistik/{$batch}/rollback");

    $this->actingAs($this->admin)->post("/admin/statistik/{$batch}/rollback")->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
    $this->actingAs($this->admin)->post('/admin/statistik/9999/rollback')->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');

    $sinkron = DB::table('import_batches')->insertGetId(['tipe' => 'sinkron_bkn', 'status' => 'sukses', 'mulai_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($this->admin)->post("/admin/statistik/{$sinkron}/rollback")->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
    expect(DB::table('import_batches')->where('id', $sinkron)->value('status'))->toBe('sukses');
});

it('mengembalikan 404 untuk unduhan galat batch yang tidak ada', function (): void {
    $this->actingAs($this->admin)->get('/admin/statistik/9999/galat')->assertNotFound();
});

it('menyediakan template CSV dengan BOM agar terbuka benar di Excel', function (): void {
    $respons = $this->actingAs($this->admin)->get('/admin/statistik/template');

    $respons->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($respons->getContent())->toStartWith("\xEF\xBB\xBFtahun,jenis_pengadaan,cakupan,instansi");
});

it('membatasi laju unggahan pratinjau', function (): void {
    foreach (range(1, 10) as $i) {
        $this->actingAs($this->admin)->post('/admin/statistik/pratinjau', ['berkas' => berkasCsv(dua())])->assertRedirect();
    }

    $this->actingAs($this->admin)->post('/admin/statistik/pratinjau', ['berkas' => berkasCsv(dua())])->assertStatus(429);
});
