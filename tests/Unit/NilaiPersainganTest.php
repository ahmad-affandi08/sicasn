<?php

declare(strict_types=1);

use Src\Formasi\Domain\AmbangPersaingan;
use Src\Formasi\Domain\NilaiPersaingan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatPersaingan;

function nilai(?int $pelamar, ?int $submit, ?int $formasi, StatusAngka $status = StatusAngka::Akhir, ?AmbangPersaingan $ambang = null)
{
    return NilaiPersaingan::nilai($pelamar, $submit, $formasi, $status, $ambang ?? AmbangPersaingan::awal());
}

it('memberi tingkat berdasarkan rasio pelamar per formasi pada angka akhir', function (int $pelamar, TingkatPersaingan $tingkat, float $rasio): void {
    $hasil = nilai($pelamar, null, 100);

    expect($hasil->tingkat)->toBe($tingkat)
        ->and($hasil->rasio)->toBe($rasio)
        ->and($hasil->batasBawah)->toBeFalse();
})->with([
    'di bawah 5 longgar' => [499, TingkatPersaingan::Longgar, 4.99],
    'tepat 5 sedang' => [500, TingkatPersaingan::Sedang, 5.0],
    'tepat 20 masih sedang' => [2000, TingkatPersaingan::Sedang, 20.0],
    'di atas 20 ketat' => [2001, TingkatPersaingan::Ketat, 20.01],
]);

it('tidak pernah menyebut longgar untuk angka sementara karena itu batas bawah', function (): void {
    $hasil = nilai(100, null, 100, StatusAngka::Sementara);

    expect($hasil->tingkat)->toBe(TingkatPersaingan::BelumFinal)
        ->and($hasil->batasBawah)->toBeTrue()
        ->and($hasil->rasio)->toBe(1.0);
});

it('tetap menyebut ketat untuk angka sementara karena angka akhir hanya bisa lebih besar', function (): void {
    $hasil = nilai(5000, null, 100, StatusAngka::Sementara);

    expect($hasil->tingkat)->toBe(TingkatPersaingan::Ketat)->and($hasil->batasBawah)->toBeTrue();
});

it('memakai jumlah submit sebagai batas bawah bila pelamar tidak tersedia', function (): void {
    $hasil = nilai(null, 1000, 100);

    expect($hasil->tingkat)->toBe(TingkatPersaingan::Sedang)->and($hasil->rasio)->toBe(10.0)->and($hasil->batasBawah)->toBeTrue();
});

it('mengubah longgar menjadi belum final bila hanya ada jumlah submit', function (): void {
    expect(nilai(null, 100, 100)->tingkat)->toBe(TingkatPersaingan::BelumFinal);
});

it('menyatakan belum ada data bila formasi atau pelamar tidak ada', function (?int $pelamar, ?int $submit, ?int $formasi): void {
    $hasil = nilai($pelamar, $submit, $formasi);

    expect($hasil->tingkat)->toBe(TingkatPersaingan::BelumAdaData)->and($hasil->rasio)->toBeNull();
})->with([
    'tanpa formasi' => [100, null, null],
    'formasi nol' => [100, null, 0],
    'tanpa pelamar dan submit' => [null, null, 100],
]);

it('memakai ambang yang diatur admin', function (): void {
    $ambang = new AmbangPersaingan(2.0, 10.0);

    expect(nilai(300, null, 100, StatusAngka::Akhir, $ambang)->tingkat)->toBe(TingkatPersaingan::Sedang)
        ->and(nilai(150, null, 100, StatusAngka::Akhir, $ambang)->tingkat)->toBe(TingkatPersaingan::Longgar)
        ->and(nilai(1100, null, 100, StatusAngka::Akhir, $ambang)->tingkat)->toBe(TingkatPersaingan::Ketat);
});

it('menolak ambang yang tidak masuk akal', function (float $longgar, float $ketat): void {
    expect(fn () => new AmbangPersaingan($longgar, $ketat))->toThrow(InvalidArgumentException::class);
})->with([
    'longgar nol' => [0.0, 10.0],
    'longgar negatif' => [-1.0, 10.0],
    'ketat sama dengan longgar' => [5.0, 5.0],
    'ketat di bawah longgar' => [8.0, 5.0],
    'ketat terlalu besar' => [5.0, 10001.0],
]);

it('memakai peserta lolos administrasi sebagai batas bawah dan tidak pernah longgar', function (): void {
    $sedang = NilaiPersaingan::nilai(null, null, 30, StatusAngka::Akhir, AmbangPersaingan::awal(), 600);
    $kecil = NilaiPersaingan::nilai(null, null, 30, StatusAngka::Akhir, AmbangPersaingan::awal(), 60);

    expect($sedang->tingkat)->toBe(TingkatPersaingan::Sedang)->and($sedang->rasio)->toBe(20.0)->and($sedang->batasBawah)->toBeTrue()
        ->and($kecil->tingkat)->toBe(TingkatPersaingan::BelumFinal);
});

it('mengutamakan pelamar lalu submit sebelum lolos administrasi', function (): void {
    expect(NilaiPersaingan::nilai(1000, 500, 10, StatusAngka::Akhir, AmbangPersaingan::awal(), 300)->rasio)->toBe(100.0)
        ->and(NilaiPersaingan::nilai(null, 500, 10, StatusAngka::Akhir, AmbangPersaingan::awal(), 300)->rasio)->toBe(50.0);
});
