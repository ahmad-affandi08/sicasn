<?php

declare(strict_types=1);

use Src\Formasi\Application\PencocokPeluangJabatan;
use Src\Formasi\Application\RingkasFormasi;
use Src\Formasi\Application\SnapshotJabatan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

function formasiUji(?int $wilayahId = 7, string $lokasi = 'Kab. Sleman'): RingkasFormasi
{
    return new RingkasFormasi(1, 3, 'Kementerian Agama', 'Penghulu Ahli Pertama', 'JF-1', 'PENGHULU AHLI PERTAMA', $lokasi, null, $wilayahId, 3, 'UMUM', false, 18, 35, true, 'S-1 HUKUM');
}

function snapshotUji(array $ubah = []): SnapshotJabatan
{
    $dasar = ['instansiId' => 3, 'namaJabatan' => 'Penghulu Ahli Pertama', 'lokasi' => null, 'wilayahId' => null, 'tanggal' => '2024-09-19', 'statusAngka' => StatusAngka::Akhir, 'formasi' => 10, 'pelamar' => null, 'submit' => null, 'lolos' => 100, 'tingkat' => TingkatData::B, 'keyakinan' => Keyakinan::Turunan, 'sumber' => 'Uji', 'url' => null, 'catatan' => null];
    $d = array_merge($dasar, $ubah);

    return new SnapshotJabatan($d['instansiId'], $d['namaJabatan'], $d['lokasi'], $d['wilayahId'], $d['tanggal'], $d['statusAngka'], $d['formasi'], $d['pelamar'], $d['submit'], $d['lolos'], $d['tingkat'], $d['keyakinan'], $d['sumber'], $d['url'], $d['catatan']);
}

it('mencocokkan data jabatan tanpa lokasi ke formasi di lokasi mana pun', function (): void {
    $dipilih = PencocokPeluangJabatan::pilih(formasiUji(), [snapshotUji()]);

    expect($dipilih)->not->toBeNull()->and($dipilih->lolosAdministrasi)->toBe(100);
});

it('mencocokkan lokasi lewat wilayah yang sama', function (): void {
    $dipilih = PencocokPeluangJabatan::pilih(formasiUji(7), [snapshotUji(['wilayahId' => 7, 'lokasi' => 'Kab. Sleman'])]);

    expect($dipilih)->not->toBeNull();
});

it('tidak mencocokkan bila wilayah berbeda sehingga konservatif', function (): void {
    expect(PencocokPeluangJabatan::pilih(formasiUji(7), [snapshotUji(['wilayahId' => 9, 'lokasi' => 'Kota Malang'])]))->toBeNull();
});

it('mencocokkan nama satuan kerja tanpa membedakan huruf besar kecil dan spasi', function (): void {
    $formasi = formasiUji(null, 'Balai Uji  |  Wilayah II');

    expect(PencocokPeluangJabatan::pilih($formasi, [snapshotUji(['lokasi' => 'balai uji | wilayah ii'])]))->not->toBeNull()
        ->and(PencocokPeluangJabatan::pilih($formasi, [snapshotUji(['lokasi' => 'Balai Uji | Wilayah I'])]))->toBeNull();
});

it('mengutamakan data spesifik lokasi daripada data jabatan umum', function (): void {
    $dipilih = PencocokPeluangJabatan::pilih(formasiUji(7), [
        snapshotUji(['lolos' => 100, 'tanggal' => '2024-09-20']),
        snapshotUji(['lolos' => 40, 'wilayahId' => 7, 'lokasi' => 'Kab. Sleman', 'tanggal' => '2024-09-19']),
    ]);

    expect($dipilih->lolosAdministrasi)->toBe(40);
});

it('memakai tanggal terbaru, lalu tingkat data C di atas B di atas A', function (): void {
    $baru = PencocokPeluangJabatan::pilih(formasiUji(), [snapshotUji(['lolos' => 1, 'tanggal' => '2024-09-01']), snapshotUji(['lolos' => 2, 'tanggal' => '2024-09-19'])]);
    $tingkat = PencocokPeluangJabatan::pilih(formasiUji(), [snapshotUji(['lolos' => 1, 'tingkat' => TingkatData::A]), snapshotUji(['lolos' => 3, 'tingkat' => TingkatData::C]), snapshotUji(['lolos' => 2, 'tingkat' => TingkatData::B])]);

    expect($baru->lolosAdministrasi)->toBe(2)->and($tingkat->lolosAdministrasi)->toBe(3);
});

it('mengembalikan null bila tidak ada data', function (): void {
    expect(PencocokPeluangJabatan::pilih(formasiUji(), []))->toBeNull();
});
