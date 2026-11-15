<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

it('menampilkan panduan singkat pada member yang baru masuk', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get('/peluang-instansi')->assertInertia(fn (Assert $halaman) => $halaman->where('panduan', true));
});

it('tidak menampilkan panduan sebelum member mengganti kata sandi sementara', function (): void {
    $member = UserModel::factory()->denganPasswordSementara()->create();
    masukSebagai($this, $member);

    $this->get('/ganti-password')->assertInertia(fn (Assert $halaman) => $halaman->where('panduan', false));
});

it('mencatat panduan sudah dilihat sehingga tidak muncul lagi', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->post('/akun/panduan-selesai')->assertRedirect();

    expect($member->fresh()->panduan_dilihat_at)->not->toBeNull();
    auth()->forgetGuards();
    $this->get('/peluang-instansi')->assertInertia(fn (Assert $halaman) => $halaman->where('panduan', false));
});

it('tidak mengubah waktu panduan yang sudah tercatat', function (): void {
    $member = UserModel::factory()->create(['panduan_dilihat_at' => '2026-01-02 03:04:05']);
    masukSebagai($this, $member);

    $this->post('/akun/panduan-selesai');

    expect($member->fresh()->panduan_dilihat_at->format('Y-m-d H:i:s'))->toBe('2026-01-02 03:04:05');
});

it('tidak pernah menampilkan panduan member kepada admin', function (): void {
    $admin = UserModel::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertInertia(fn (Assert $halaman) => $halaman->where('panduan', false));
});

it('menolak tamu yang mencoba menandai panduan', function (): void {
    $this->post('/akun/panduan-selesai')->assertRedirect('/masuk');
});
