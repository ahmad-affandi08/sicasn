<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\SimpanPengaturanWhatsapp;
use Tests\Support\PembangunMailerPalsu;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

it('melarang member dan tamu mengakses panel admin', function (): void {
    $this->get('/admin/pengguna')->assertRedirect('/masuk');

    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get('/admin')->assertForbidden();
    $this->get('/admin/pengguna')->assertForbidden();
    $this->post('/admin/pengguna', ['nama' => 'X', 'email' => 'x@contoh.id'])->assertForbidden();
});

it('menampilkan dasbor dengan ringkasan dan daftar kesiapan', function (): void {
    UserModel::factory()->count(2)->create();
    UserModel::factory()->nonaktif()->create();

    $this->actingAs($this->admin)->get('/admin')->assertOk()->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Dasbor')
        ->where('ringkasan.aktif', 2)
        ->where('ringkasan.nonaktif', 1)
        ->where('kesiapan', ['email' => false, 'whatsapp' => false, 'pembayaran' => false, 'sinkron' => false]));
});

it('membuat akun member, mengantrekan email kredensial, dan mencatat audit', function (): void {
    pasangMailerPalsu();
    konfigurasiEmailTersimpan();

    $respons = $this->actingAs($this->admin)->post('/admin/pengguna', [
        'nama' => 'Siti Aminah',
        'email' => 'Siti@Contoh.ID',
        'no_whatsapp' => '081234567890',
        'catatan_pembayaran' => 'TF 14/11 BCA',
    ]);

    $member = UserModel::query()->where('email', 'siti@contoh.id')->firstOrFail();

    $respons->assertRedirect("/admin/pengguna/{$member->id}")->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    expect($member->aktif)->toBeTrue()
        ->and($member->wajib_ganti_password)->toBeTrue()
        ->and($member->password_sementara_berlaku_sampai->isFuture())->toBeTrue()
        ->and($member->catatan_pembayaran)->toBe('TF 14/11 BCA')
        ->and($member->dibuat_oleh)->toBe($this->admin->id);

    expect(DB::table('audit_logs')->where('aksi', 'pengguna.dibuat')->where('target_id', $member->id)->exists())->toBeTrue();
    expect(DB::table('email_logs')->where('user_id', $member->id)->value('status'))->toBe('terkirim');
});

it('mengirim email berisi kata sandi sementara yang benar-benar bisa dipakai masuk', function (): void {
    pasangMailerPalsu();
    konfigurasiEmailTersimpan();
    app(SimpanPengaturanWhatsapp::class)->handle('081234567890', 'Admin', '', 1);

    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => 'Budi', 'email' => 'budi@contoh.id']);
    auth()->logout();

    $pesan = PembangunMailerPalsu::pesanTerkirim();
    expect($pesan)->toHaveCount(1);

    $email = $pesan[0]->getOriginalMessage();
    $html = $email->getHtmlBody();

    preg_match('/<code>([^<]+)<\/code>/', $html, $cocok);
    $password = html_entity_decode($cocok[1] ?? '');

    expect($email->getTo()[0]->getAddress())->toBe('budi@contoh.id')
        ->and($email->getSubject())->toBe('Akun SICASN Anda sudah siap')
        ->and($email->getFrom()[0]->getAddress())->toBe('noreply@contoh.id')
        ->and($password)->toHaveLength(14)
        ->and($html)->toContain('budi@contoh.id')
        ->and($html)->toContain('wa.me/6281234567890')
        ->and($html)->toContain('http://localhost/masuk');

    $this->post('/masuk', ['email' => 'budi@contoh.id', 'password' => $password])->assertRedirect('/peluang-instansi');
    $this->get('/akun')->assertRedirect('/ganti-password');
});

it('tetap membuat akun tetapi menandai email gagal bila email belum dikonfigurasi', function (): void {
    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => 'Dewi', 'email' => 'dewi@contoh.id']);

    $member = UserModel::query()->where('email', 'dewi@contoh.id')->firstOrFail();
    $log = DB::table('email_logs')->where('user_id', $member->id)->first();

    expect($log->status)->toBe('gagal')
        ->and($log->error)->toContain('belum dikonfigurasi');

    $this->actingAs($this->admin)->get("/admin/pengguna/{$member->id}")->assertInertia(fn (Assert $h) => $h
        ->where('pengguna.statusEmail', 'gagal')
        ->where('pengguna.galatEmail', fn (string $galat) => str_contains($galat, 'belum dikonfigurasi')));
});

it('menyimpan kata sandi sementara hanya sebagai hash, tidak pernah polos', function (): void {
    pasangMailerPalsu();
    konfigurasiEmailTersimpan();

    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => 'Eko', 'email' => 'eko@contoh.id']);

    $html = PembangunMailerPalsu::pesanTerkirim()[0]->getOriginalMessage()->getHtmlBody();
    preg_match('/<code>([^<]+)<\/code>/', $html, $cocok);
    $password = html_entity_decode($cocok[1]);

    $hash = UserModel::query()->where('email', 'eko@contoh.id')->value('password');

    expect($hash)->not->toBe($password)->and(Hash::check($password, $hash))->toBeTrue();
    expect(DB::table('audit_logs')->get()->toJson())->not->toContain($password);
    expect(DB::table('email_logs')->get()->toJson())->not->toContain($password);
});

it('menolak email yang sudah terdaftar', function (): void {
    UserModel::factory()->create(['email' => 'ada@contoh.id']);

    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => 'Dobel', 'email' => 'ADA@contoh.id'])->assertSessionHasErrors('email');

    expect(UserModel::query()->where('email', 'ada@contoh.id')->count())->toBe(1);
});

it('memvalidasi isian pembuatan akun', function (): void {
    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => '', 'email' => 'bukan-email'])->assertSessionHasErrors(['nama', 'email']);
});

it('mengirim ulang kredensial dengan kata sandi baru dan membatalkan yang lama', function (): void {
    pasangMailerPalsu();
    konfigurasiEmailTersimpan();
    $member = UserModel::factory()->denganPasswordSementara()->create();
    $hashLama = $member->password;

    $this->actingAs($this->admin)->post("/admin/pengguna/{$member->id}/kirim-ulang")->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $member->refresh();

    expect($member->password)->not->toBe($hashLama)
        ->and($member->wajib_ganti_password)->toBeTrue()
        ->and(PembangunMailerPalsu::pesanTerkirim())->toHaveCount(1)
        ->and(DB::table('audit_logs')->where('aksi', 'kredensial.dikirim_ulang')->exists())->toBeTrue();
});

it('menonaktifkan member dengan alasan lalu menolak login-nya, dan bisa diaktifkan lagi', function (): void {
    $member = UserModel::factory()->create();

    $this->actingAs($this->admin)->post("/admin/pengguna/{$member->id}/nonaktifkan", ['alasan' => 'Belum bayar'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $member->refresh();
    expect($member->aktif)->toBeFalse()->and($member->alasan_nonaktif)->toBe('Belum bayar');

    auth()->logout();
    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])
        ->assertSessionHas('dialog', fn (array $d) => $d['judul'] === 'Akun Anda sedang nonaktif');
    $this->assertGuest();

    $this->actingAs($this->admin)->post("/admin/pengguna/{$member->id}/aktifkan");
    $member->refresh();
    expect($member->aktif)->toBeTrue()->and($member->alasan_nonaktif)->toBeNull();

    auth()->logout();
    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])->assertRedirect('/peluang-instansi');
});

it('tidak mengizinkan menonaktifkan admin atau akun sendiri', function (): void {
    $adminLain = UserModel::factory()->admin()->create();

    foreach ([$adminLain, $this->admin] as $target) {
        $this->actingAs($this->admin)->post("/admin/pengguna/{$target->id}/nonaktifkan")
            ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
        expect($target->fresh()->aktif)->toBeTrue();
    }
});

it('mencari dan menyaring daftar pengguna', function (): void {
    UserModel::factory()->create(['name' => 'Ani Lestari', 'email' => 'ani@contoh.id']);
    UserModel::factory()->nonaktif()->create(['name' => 'Bima Santoso', 'email' => 'bima@contoh.id']);

    $this->actingAs($this->admin)->get('/admin/pengguna?cari=bima')->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Pengguna/Index')->has('pengguna', 1)->where('pengguna.0.email', 'bima@contoh.id'));

    $this->actingAs($this->admin)->get('/admin/pengguna?status=nonaktif')->assertInertia(fn (Assert $h) => $h->has('pengguna', 1));
    $this->actingAs($this->admin)->get('/admin/pengguna?status=aktif')->assertInertia(fn (Assert $h) => $h->has('pengguna', 2));
});

it('menampilkan riwayat audit pada halaman detail pengguna', function (): void {
    $member = UserModel::factory()->create();
    $this->actingAs($this->admin)->post("/admin/pengguna/{$member->id}/nonaktifkan", ['alasan' => 'Uji']);

    $this->actingAs($this->admin)->get("/admin/pengguna/{$member->id}")->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Pengguna/Tampil')
        ->where('riwayat.0.aksi', 'pengguna.dinonaktifkan')
        ->where('riwayat.0.aktor', $this->admin->name));
});

it('tidak membocorkan hash kata sandi ke browser', function (): void {
    $member = UserModel::factory()->create();

    $respons = $this->actingAs($this->admin)->get("/admin/pengguna/{$member->id}");

    expect($respons->getContent())->not->toContain($member->password);
});
