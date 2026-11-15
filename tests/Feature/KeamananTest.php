<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

it('memasang header keamanan pada setiap halaman', function (): void {
    $respons = $this->get('/');

    $respons->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    expect($respons->headers->get('Permissions-Policy'))->toContain('camera=()')
        ->and($respons->headers->get('Cross-Origin-Opener-Policy'))->toBe('same-origin')
        ->and($respons->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('menyusun kebijakan konten yang ketat dengan nonce acak per permintaan', function (): void {
    $pertama = (string) $this->get('/')->headers->get('Content-Security-Policy');
    $kedua = (string) $this->get('/')->headers->get('Content-Security-Policy');

    expect($pertama)->toContain("default-src 'self'")
        ->and($pertama)->toContain("frame-ancestors 'none'")
        ->and($pertama)->toContain("object-src 'none'")
        ->and($pertama)->toContain("form-action 'self'")
        ->and($pertama)->toContain("base-uri 'self'")
        ->and($pertama)->not->toContain('unsafe-eval')
        ->and($pertama)->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9]{24}'/")
        ->and($pertama)->not->toBe($kedua);
});

it('hanya mengizinkan font dan skrip dari asal sendiri tanpa Google Fonts', function (): void {
    $kebijakan = (string) $this->get('/')->headers->get('Content-Security-Policy');

    expect($kebijakan)->not->toContain('googleapis')
        ->and($kebijakan)->not->toContain('gstatic')
        ->and($kebijakan)->toContain("font-src 'self'");
});

it('mengirim HSTS hanya lewat koneksi aman, termasuk di belakang proxy tepercaya', function (): void {
    $this->get('/')->assertHeaderMissing('Strict-Transport-Security');

    $this->get('/', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.9'])
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('mengarahkan pengguna yang sudah masuk dari halaman masuk sesuai perannya', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);
    $this->get('/masuk')->assertRedirect('/peluang-instansi');
});

it('mengarahkan admin yang sudah masuk dari halaman masuk ke dasbor admin', function (): void {
    $admin = UserModel::factory()->admin()->create();
    $this->actingAs($admin)->get('/masuk')->assertRedirect('/admin');
});

it('menampilkan halaman galat 404 yang ramah untuk alamat yang tidak ada', function (): void {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $halaman) => $halaman->component('Galat')->where('kode', 404));
});

it('menampilkan halaman galat 403 untuk member yang membuka area admin', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get('/admin')
        ->assertForbidden()
        ->assertInertia(fn (Assert $halaman) => $halaman->component('Galat')->where('kode', 403));
});

it('tetap memberi galat JSON untuk klien yang meminta JSON', function (): void {
    $this->getJson('/halaman-yang-tidak-ada')->assertNotFound()->assertJsonStructure(['message']);
});

it('menampilkan halaman galat 500 ramah tanpa membocorkan detail saat debug mati', function (): void {
    config(['app.debug' => false]);
    app('router')->get('/uji-galat-server', fn () => throw new RuntimeException('rahasia internal'));

    $respons = $this->get('/uji-galat-server');

    $respons->assertStatus(500)->assertInertia(fn (Assert $halaman) => $halaman->component('Galat')->where('kode', 500));
    expect($respons->getContent())->not->toContain('rahasia internal');
});

it('menampilkan kebijakan privasi tanpa perlu masuk', function (): void {
    $this->get('/privasi')->assertOk()->assertInertia(fn (Assert $halaman) => $halaman->component('Privasi'));
});

it('menyertakan header keamanan dan nonce yang sah juga pada halaman 404', function (): void {
    $respons = $this->get('/halaman-yang-tidak-ada');

    $respons->assertNotFound()->assertHeader('X-Frame-Options', 'DENY');
    expect((string) $respons->headers->get('Content-Security-Policy'))->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9]{24}'/");
});

it('membagikan data bersama yang aman pada galat yang terjadi sebelum sesi dimulai', function (): void {
    $this->get('/halaman-yang-tidak-ada')->assertInertia(fn (Assert $halaman) => $halaman
        ->where('auth.pengguna', null)
        ->where('aplikasi.nama', 'SICASN'));
});

it('menampilkan nama pengguna yang benar pada 404 untuk yang sudah masuk', function (): void {
    $member = UserModel::factory()->create(['name' => 'Siti Aminah']);
    masukSebagai($this, $member);

    $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertInertia(fn (Assert $halaman) => $halaman
        ->where('auth.pengguna.nama', 'Siti Aminah'));
});

it('tetap bisa menampilkan halaman perawatan 503 tanpa sesi dan tanpa membocorkan apa pun', function (): void {
    config(['app.debug' => false]);
    app('router')->get('/uji-perawatan', fn () => abort(503));

    $this->get('/uji-perawatan')
        ->assertStatus(503)
        ->assertInertia(fn (Assert $halaman) => $halaman->component('Galat')->where('kode', 503)->where('auth.pengguna', null)->where('panduan', false));
});

it('menandai cookie sesi Secure hanya pada koneksi HTTPS', function (): void {
    config(['session.driver' => 'cookie']);

    $cookieHttp = collect($this->get('/')->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
    $cookieHttps = collect($this->get('/', ['X-Forwarded-Proto' => 'https'])->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

    expect($cookieHttps)->not->toBeNull()
        ->and($cookieHttps->isSecure())->toBeTrue()
        ->and($cookieHttps->isHttpOnly())->toBeTrue()
        ->and($cookieHttp->isSecure())->toBeFalse();
});

it('mengubah galat 429 pada aksi dari halaman menjadi dialog ramah, bukan berpindah halaman', function (): void {
    app('router')->middleware(['web', 'throttle:1,1'])->post('/uji-throttle', fn () => 'ok');

    $this->post('/uji-throttle', [], ['X-Inertia' => 'true'])->assertOk();

    $this->post('/uji-throttle', [], ['X-Inertia' => 'true'])
        ->assertRedirect()
        ->assertSessionHas('dialog.judul', 'Terlalu banyak permintaan');
});

it('tetap menampilkan halaman galat 429 untuk navigasi biasa', function (): void {
    app('router')->middleware(['web', 'throttle:1,1'])->get('/uji-throttle-halaman', fn () => 'ok');

    $this->get('/uji-throttle-halaman')->assertOk();

    $this->get('/uji-throttle-halaman')->assertStatus(429)->assertInertia(fn (Assert $halaman) => $halaman->component('Galat')->where('kode', 429));
});

it('mengubah galat server pada aksi dari halaman menjadi dialog tanpa membocorkan detail', function (): void {
    config(['app.debug' => false]);
    app('router')->middleware('web')->post('/uji-galat-aksi', fn () => throw new RuntimeException('rahasia internal'));

    $respons = $this->post('/uji-galat-aksi', [], ['X-Inertia' => 'true']);

    $respons->assertRedirect()->assertSessionHas('dialog.judul', 'Terjadi gangguan di server');
    expect(json_encode(session('dialog')))->not->toContain('rahasia internal');
});
