<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Src\DataImport\Application\SimpanPengaturanSinkron;
use Src\DataImport\Application\SyncFormasiFromBkn;
use Src\DataImport\Infrastructure\Queue\JalankanSinkronBkn;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

function sinkronkan(): void
{
    app(SyncFormasiFromBkn::class)->handle(null);
}

it('melarang member dan tamu membuka halaman sinkron', function (): void {
    $this->get('/admin/sinkron')->assertRedirect('/masuk');

    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get('/admin/sinkron')->assertForbidden();
    $this->post('/admin/sinkron/jalankan')->assertForbidden();
    $this->put('/admin/sinkron/pengaturan', ['url' => 'https://contoh.id', 'frekuensi' => 'harian'])->assertForbidden();
});

it('menampilkan status kosong sebelum ada sinkron', function (): void {
    $this->actingAs($this->admin)->get('/admin/sinkron')->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Sinkron')
        ->where('terakhirBerhasil', null)
        ->where('formasiAktif', 0)
        ->where('jenisAktif', ['CPNS'])
        ->where('sedangBerjalan', false)
        ->where('pengaturan.url', 'https://api-rekrutmen.bkn.go.id/portal/api/spf')
        ->where('pengaturan.frekuensi', 'harian')
        ->has('frekuensi', 2)
        ->has('riwayat', 0)
        ->has('event', 0));
});

it('menampilkan ringkasan sinkron, event, dan riwayat setelah sinkron berjalan', function (): void {
    fakeBkn(barisBkn());
    sinkronkan();

    $this->actingAs($this->admin)->get('/admin/sinkron')->assertInertia(fn (Assert $h) => $h
        ->where('formasiAktif', 5)
        ->where('terakhirBerhasil.status', 'sukses')
        ->where('terakhirBerhasil.ringkasan.baru', 5)
        ->where('terakhirBerhasil.ringkasan.diabaikan', 1)
        ->has('riwayat', 1)
        ->has('event', 2)
        ->where('event.0.diabaikan', fn (bool $nilai) => is_bool($nilai)));
});

it('menjadwalkan sinkron manual lewat antrean dan mencatatnya di audit', function (): void {
    Queue::fake();

    $this->actingAs($this->admin)->post('/admin/sinkron/jalankan')
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses' && $d['judul'] === 'Sinkron dijadwalkan');

    Queue::assertPushed(JalankanSinkronBkn::class, 1);

    expect(DB::table('audit_logs')->where('aksi', 'sinkron.diminta')->where('aktor_id', $this->admin->id)->exists())->toBeTrue();
});

it('tidak menjadwalkan sinkron baru selama sinkron lain masih berjalan', function (): void {
    Queue::fake();
    DB::table('import_batches')->insert(['tipe' => 'sinkron_bkn', 'status' => 'berjalan', 'mulai_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($this->admin)->post('/admin/sinkron/jalankan')
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan');

    Queue::assertNothingPushed();
});

it('menjalankan job sinkron manual hingga selesai', function (): void {
    fakeBkn(barisBkn());

    JalankanSinkronBkn::dispatchSync($this->admin->id);

    expect(DB::table('formasis')->count())->toBe(5)
        ->and(DB::table('import_batches')->value('dibuat_oleh'))->toBe($this->admin->id);
});

it('menyimpan pengaturan sinkron dan mencatat host tanpa membocorkan URL lengkap di audit', function (): void {
    $this->actingAs($this->admin)->put('/admin/sinkron/pengaturan', ['url' => 'https://api.contoh.go.id/v2/formasi?kunci=rahasia', 'frekuensi' => 'enam_jam'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $this->actingAs($this->admin)->get('/admin/sinkron')->assertInertia(fn (Assert $h) => $h
        ->where('pengaturan.url', 'https://api.contoh.go.id/v2/formasi?kunci=rahasia')
        ->where('pengaturan.frekuensi', 'enam_jam'));

    $audit = DB::table('audit_logs')->where('aksi', 'pengaturan.sinkron_disimpan')->value('rincian');

    expect($audit)->toContain('api.contoh.go.id')->and($audit)->not->toContain('rahasia');
});

it('menolak pengaturan sinkron yang tidak valid', function (array $isian, string $kolom): void {
    $this->actingAs($this->admin)->put('/admin/sinkron/pengaturan', $isian)->assertSessionHasErrors($kolom);
})->with([
    'bukan https' => [['url' => 'http://api.contoh.go.id/x', 'frekuensi' => 'harian'], 'url'],
    'bukan url' => [['url' => 'bukan-url', 'frekuensi' => 'harian'], 'url'],
    'kosong' => [['url' => '', 'frekuensi' => 'harian'], 'url'],
    'frekuensi asing' => [['url' => 'https://api.contoh.go.id/x', 'frekuensi' => 'setiap-menit'], 'frekuensi'],
]);

it('memakai alamat sumber dari pengaturan saat sinkron', function (): void {
    app(SimpanPengaturanSinkron::class)->handle('https://api.contoh.go.id/formasi', 'harian', $this->admin->id);
    fakeBkn(barisBkn());

    sinkronkan();

    Http::assertSent(fn ($permintaan) => $permintaan->url() === 'https://api.contoh.go.id/formasi');
});

it('menampilkan kartu sinkron dan kesiapan di dasbor', function (): void {
    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h
        ->where('kesiapan.sinkron', false)
        ->where('sinkron.terakhir', null)
        ->where('sinkron.formasiAktif', 0)
        ->has('eventBaru', 0));

    fakeBkn(barisBkn());
    sinkronkan();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h
        ->where('kesiapan.sinkron', true)
        ->where('sinkron.formasiAktif', 5)
        ->where('sinkron.terakhir.status', 'sukses'));
});

it('memberi tahu event CPNS baru di dasbor sekali saja', function (): void {
    fakeBkn(barisBkn());
    sinkronkan();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h
        ->has('eventBaru', 1)
        ->where('eventBaru.0.nama', 'Seleksi CPNS Sintetis (fixture uji)'));

    $this->actingAs($this->admin)->post('/admin/sinkron/event-diberitahukan')->assertRedirect();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h->has('eventBaru', 0));
});

it('tidak memberi tahu event yang hanya berisi seleksi yang diabaikan', function (): void {
    fakeBkn([barisBkn()[5]]);
    sinkronkan();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h->has('eventBaru', 0));
});

it('menjalankan perintah artisan sinkron dan melaporkan hasilnya', function (): void {
    fakeBkn(barisBkn());

    $this->artisan('sicasn:sync-formasi')
        ->expectsOutputToContain('5 formasi CPNS diproses, 5 baru')
        ->assertExitCode(0);
});

it('mengembalikan kode gagal pada perintah artisan saat sumber menolak', function (): void {
    fakeBknMentah('x', 403);

    $this->artisan('sicasn:sync-formasi')
        ->expectsOutputToContain('HTTP 403')
        ->assertExitCode(1);
});

it('menjadwalkan sinkron harian, sinkron enam jam, dan pemrosesan antrean', function (): void {
    $acara = collect(app(Schedule::class)->events())->keyBy(fn ($e) => $e->description);

    expect($acara->keys()->all())->toContain('sinkron-harian', 'sinkron-enam-jam', 'antrean')
        ->and($acara['sinkron-harian']->expression)->toBe('0 2 * * *')
        ->and($acara['sinkron-enam-jam']->expression)->toBe('0 */6 * * *')
        ->and($acara['antrean']->expression)->toBe('* * * * *')
        ->and($acara['antrean']->command)->toContain('queue:work --stop-when-empty');
});

it('hanya mengaktifkan jadwal yang sesuai dengan frekuensi yang dipilih admin', function (): void {
    $acara = fn () => collect(app(Schedule::class)->events())->keyBy(fn ($e) => $e->description);
    $lolos = fn (string $nama) => $acara()[$nama]->filtersPass(app());

    expect($lolos('sinkron-harian'))->toBeTrue()->and($lolos('sinkron-enam-jam'))->toBeFalse();

    app(SimpanPengaturanSinkron::class)->handle('https://api.contoh.go.id/x', 'enam_jam', $this->admin->id);

    expect($lolos('sinkron-harian'))->toBeFalse()->and($lolos('sinkron-enam-jam'))->toBeTrue();
});

it('menampilkan jumlah formasi dengan lokasi yang belum terpetakan', function (): void {
    $baris = barisCpns();
    $baris[0]['lokasi_formasi'] = 'Kab. Atlantis';
    fakeBkn($baris);
    sinkronkan();

    $this->actingAs($this->admin)->get('/admin/sinkron')->assertInertia(fn (Assert $h) => $h
        ->where('lokasiTidakTerpetakan.geografis', 1)
        ->where('lokasiTidakTerpetakan.satuanKerja', 1));
});
