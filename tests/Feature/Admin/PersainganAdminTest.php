<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

it('menampilkan ambang bawaan', function (): void {
    $this->actingAs($this->admin)->get('/admin/pengaturan/persaingan')->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Pengaturan/Persaingan')
        ->where('pengaturan.longgarMaks', 5)
        ->where('pengaturan.ketatMin', 20));
});

it('menyimpan ambang baru dan mencatat audit', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/persaingan', ['longgar_maks' => '3.5', 'ketat_min' => '40'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $this->actingAs($this->admin)->get('/admin/pengaturan/persaingan')->assertInertia(fn (Assert $h) => $h
        ->where('pengaturan.longgarMaks', 3.5)
        ->where('pengaturan.ketatMin', 40));

    expect(jumlah('audit_logs', ['aksi' => 'pengaturan.persaingan_disimpan']))->toBe(1);
});

it('menolak ambang yang tidak masuk akal dengan galat di bawah isian', function (array $input, string $kolom): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/persaingan', $input)->assertSessionHasErrors($kolom);
})->with([
    'ketat sama dengan longgar' => [['longgar_maks' => '10', 'ketat_min' => '10'], 'ketat_min'],
    'ketat di bawah longgar' => [['longgar_maks' => '10', 'ketat_min' => '5'], 'ketat_min'],
    'longgar nol' => [['longgar_maks' => '0', 'ketat_min' => '5'], 'longgar_maks'],
    'longgar bukan angka' => [['longgar_maks' => 'abc', 'ketat_min' => '5'], 'longgar_maks'],
    'ketat kosong' => [['longgar_maks' => '5', 'ketat_min' => ''], 'ketat_min'],
    'ketat terlalu besar' => [['longgar_maks' => '5', 'ketat_min' => '20000'], 'ketat_min'],
]);

it('kembali ke ambang bawaan bila nilai tersimpan rusak', function (): void {
    DB::table('pengaturans')->insert(['grup' => 'persaingan', 'kunci' => 'longgar_maks', 'nilai' => '50', 'terenkripsi' => false, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('pengaturans')->insert(['grup' => 'persaingan', 'kunci' => 'ketat_min', 'nilai' => '10', 'terenkripsi' => false, 'created_at' => now(), 'updated_at' => now()]);
    cache()->flush();

    $this->actingAs($this->admin)->get('/admin/pengaturan/persaingan')->assertInertia(fn (Assert $h) => $h
        ->where('pengaturan.longgarMaks', 5)
        ->where('pengaturan.ketatMin', 20));
});
