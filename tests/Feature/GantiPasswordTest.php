<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Src\Identity\Infrastructure\Persistence\UserModel;

it('mengganti kata sandi sementara dan membuka akses penuh', function (): void {
    $member = UserModel::factory()->denganPasswordSementara()->create();
    masukSebagai($this, $member);

    $this->post('/ganti-password', [
        'password_lama' => 'password',
        'password' => 'KataSandiBaru2026',
        'password_confirmation' => 'KataSandiBaru2026',
    ])->assertRedirect('/peluang-instansi')->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $member->refresh();
    auth()->forgetGuards();

    expect(Hash::check('KataSandiBaru2026', $member->password))->toBeTrue()
        ->and($member->wajib_ganti_password)->toBeFalse()
        ->and($member->password_sementara_berlaku_sampai)->toBeNull();

    $this->get('/akun')->assertOk();
});

it('menolak bila kata sandi saat ini salah', function (): void {
    $member = UserModel::factory()->denganPasswordSementara()->create();
    masukSebagai($this, $member);

    $this->post('/ganti-password', [
        'password_lama' => 'bukan-yang-benar',
        'password' => 'KataSandiBaru2026',
        'password_confirmation' => 'KataSandiBaru2026',
    ])->assertSessionHasErrors('password_lama');

    expect($member->fresh()->wajib_ganti_password)->toBeTrue();
});

it('menolak kata sandi baru yang lemah, tidak cocok, atau sama dengan yang lama', function (array $masukan, string $kolom): void {
    $member = UserModel::factory()->denganPasswordSementara()->create();
    masukSebagai($this, $member);

    $this->post('/ganti-password', $masukan)->assertSessionHasErrors($kolom);
})->with([
    'terlalu pendek' => [['password_lama' => 'password', 'password' => 'abc123', 'password_confirmation' => 'abc123'], 'password'],
    'tanpa angka' => [['password_lama' => 'password', 'password' => 'hurufsajapanjang', 'password_confirmation' => 'hurufsajapanjang'], 'password'],
    'konfirmasi beda' => [['password_lama' => 'password', 'password' => 'KataSandiBaru2026', 'password_confirmation' => 'Lain12345678'], 'password'],
    'sama dengan lama' => [['password_lama' => 'password', 'password' => 'password', 'password_confirmation' => 'password'], 'password'],
]);

it('boleh diakses member yang sudah mengganti kata sandi untuk mengubahnya lagi', function (): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get('/ganti-password')->assertOk();
});
