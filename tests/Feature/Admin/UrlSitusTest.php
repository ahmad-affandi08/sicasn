<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\AmbilPengaturanSitus;
use Src\Pengaturan\Application\AmbilTautanMasuk;
use Src\Pengaturan\Application\AturUrlSitus;
use Src\Pengaturan\Application\SimpanPengaturanWhatsapp;
use Tests\Support\PembangunMailerPalsu;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

it('menyimpan alamat situs tanpa garis miring penutup dan membacanya kembali', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => true, 'url' => 'https://sicasn.contoh.id/'])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->get('/admin/pengaturan/situs')->assertInertia(fn (Assert $halaman) => $halaman->where('pengaturan.url', 'https://sicasn.contoh.id'));
});

it('menolak alamat situs yang bukan http atau https', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => true, 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => true, 'url' => 'bukan url'])->assertSessionHasErrors('url');
});

it('mengosongkan alamat situs bila kolom dikosongkan', function (): void {
    app(AturUrlSitus::class)->handle('https://sicasn.contoh.id');

    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => true, 'url' => ''])->assertSessionHasNoErrors();

    expect(app(AmbilPengaturanSitus::class)->handle()->url)->toBeNull();
});

it('memakai alamat cadangan untuk tautan masuk selama alamat situs belum diisi', function (): void {
    expect(app(AmbilTautanMasuk::class)->handle('http://localhost/masuk'))->toBe('http://localhost/masuk');

    app(AturUrlSitus::class)->handle('https://sicasn.contoh.id/');

    expect(app(AmbilTautanMasuk::class)->handle('http://localhost/masuk'))->toBe('https://sicasn.contoh.id/masuk');
});

it('mempertahankan pengaturan satu sesi saat alamat situs diatur dari perintah instal', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => false, 'url' => '']);

    app(AturUrlSitus::class)->handle('https://sicasn.contoh.id');

    expect(app(AmbilPengaturanSitus::class)->handle()->satuSesi)->toBeFalse();
});

it('menolak alamat yang tidak valid pada pengaturan awal', function (): void {
    expect(fn () => app(AturUrlSitus::class)->handle('ftp://contoh.id'))->toThrow(InvalidArgumentException::class);
});

it('menaruh alamat situs yang tersimpan pada tautan masuk di email kredensial', function (): void {
    pasangMailerPalsu();
    konfigurasiEmailTersimpan();
    app(SimpanPengaturanWhatsapp::class)->handle('081234567890', 'Admin', '', 1);
    app(AturUrlSitus::class)->handle('https://sicasn.contoh.id');

    $this->actingAs($this->admin)->post('/admin/pengguna', ['nama' => 'Budi', 'email' => 'budi@contoh.id']);

    $html = PembangunMailerPalsu::pesanTerkirim()[0]->getOriginalMessage()->getHtmlBody();

    expect($html)->toContain('https://sicasn.contoh.id/masuk')->and($html)->not->toContain('http://localhost/masuk');
});
