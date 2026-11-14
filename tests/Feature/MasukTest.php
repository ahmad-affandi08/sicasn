<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\SimpanPengaturanSitus;
use Src\Pengaturan\Application\SimpanPengaturanWhatsapp;

it('menampilkan halaman masuk untuk tamu', function (): void {
    $this->get('/masuk')->assertOk()->assertInertia(fn (Assert $halaman) => $halaman->component('Masuk'));
});

it('mengarahkan tamu yang membuka halaman member ke halaman masuk', function (): void {
    $this->get('/akun')->assertRedirect('/masuk');
    $this->get('/admin')->assertRedirect('/masuk');
});

it('memasukkan member ke halaman akun', function (): void {
    $member = UserModel::factory()->create();

    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])->assertRedirect('/akun');
    $this->assertAuthenticatedAs($member);
});

it('memasukkan admin ke dasbor admin', function (): void {
    $admin = UserModel::factory()->admin()->create();

    $this->post('/masuk', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
});

it('tidak membedakan huruf besar dan kecil pada email', function (): void {
    $member = UserModel::factory()->create(['email' => 'budi@contoh.id']);

    $this->post('/masuk', ['email' => 'BUDI@Contoh.ID', 'password' => 'password'])->assertRedirect('/akun');
});

it('menolak kata sandi salah tanpa membocorkan apakah email terdaftar', function (): void {
    $member = UserModel::factory()->create();

    $salahPassword = $this->post('/masuk', ['email' => $member->email, 'password' => 'salah-salah']);
    $emailTakDikenal = $this->post('/masuk', ['email' => 'tidakada@contoh.id', 'password' => 'salah-salah']);

    $salahPassword->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak cocok.']);
    $emailTakDikenal->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak cocok.']);
    $this->assertGuest();
});

it('menolak akun nonaktif dengan dialog ramah berisi tautan WhatsApp', function (): void {
    app(SimpanPengaturanWhatsapp::class)->handle('081234567890', 'Admin', '', 1);
    $member = UserModel::factory()->nonaktif('Belum bayar')->create();

    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])
        ->assertSessionHas('dialog', fn (array $dialog) => $dialog['jenis'] === 'peringatan'
            && $dialog['judul'] === 'Akun Anda sedang nonaktif'
            && str_starts_with($dialog['tautan']['url'], 'https://wa.me/6281234567890'));
    $this->assertGuest();
});

it('menolak kata sandi sementara yang sudah kedaluwarsa', function (): void {
    $member = UserModel::factory()->denganPasswordSementara(now()->subMinute())->create();

    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])
        ->assertSessionHas('dialog', fn (array $dialog) => $dialog['judul'] === 'Kata sandi sementara kedaluwarsa');
    $this->assertGuest();
});

it('membatasi percobaan masuk setelah lima kali gagal', function (): void {
    $member = UserModel::factory()->create();

    foreach (range(1, 5) as $ke) {
        $this->post('/masuk', ['email' => $member->email, 'password' => 'salah-salah']);
    }

    $this->post('/masuk', ['email' => $member->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('memaksa penggantian kata sandi sementara sebelum mengakses halaman lain', function (): void {
    $member = UserModel::factory()->denganPasswordSementara()->create();

    masukSebagai($this, $member);

    $this->get('/akun')->assertRedirect('/ganti-password');
    $this->get('/ganti-password')->assertOk()->assertInertia(fn (Assert $halaman) => $halaman->component('GantiPassword')->where('wajib', true));
});

it('keluar dan menghapus sesi aktif', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->post('/keluar')->assertRedirect('/');

    $this->assertGuest();
    expect($member->fresh()->sesi_aktif_id)->toBeNull();
});

it('mengeluarkan akun yang dinonaktifkan saat sesinya masih berjalan', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $member->forceFill(['aktif' => false])->save();
    auth()->forgetGuards();

    $this->get('/akun')->assertRedirect('/masuk')->assertSessionHas('dialog', fn (array $d) => $d['judul'] === 'Akun Anda sedang nonaktif');
    $this->assertGuest();
});

it('mengeluarkan sesi lama bila akun dipakai di perangkat lain', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $member->forceFill(['sesi_aktif_id' => 'sesi-perangkat-lain'])->save();
    auth()->forgetGuards();

    $this->get('/akun')->assertRedirect('/masuk')->assertSessionHas('dialog', fn (array $d) => $d['judul'] === 'Akun dipakai di perangkat lain');
});

it('membiarkan banyak sesi bila pengaturan satu sesi dimatikan', function (): void {
    app(SimpanPengaturanSitus::class)->handle(false, 1);
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $member->forceFill(['sesi_aktif_id' => 'sesi-perangkat-lain'])->save();
    auth()->forgetGuards();

    $this->get('/akun')->assertOk();
});
