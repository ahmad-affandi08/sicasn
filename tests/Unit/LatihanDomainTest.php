<?php

declare(strict_types=1);

use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\ModeLatihan;
use Src\Latihan\Domain\PassingGrade;

it('memakai komposisi resmi SKD: 30 TWK, 35 TIU, 45 TKP dengan skor maksimum 150, 175, dan 225', function (): void {
    expect(ModeLatihan::Simulasi->komposisi())->toBe(['twk' => 30, 'tiu' => 35, 'tkp' => 45])
        ->and(KategoriSoal::Twk->skorMaksResmi())->toBe(150)
        ->and(KategoriSoal::Tiu->skorMaksResmi())->toBe(175)
        ->and(KategoriSoal::Tkp->skorMaksResmi())->toBe(225);
});

it('memberi durasi 100 menit untuk simulasi penuh dan sebanding untuk simulasi yang lebih kecil', function (): void {
    expect(ModeLatihan::Simulasi->durasiDetik(110))->toBe(6000)
        ->and(ModeLatihan::Simulasi->durasiDetik(55))->toBe(3000);
});

it('memberi 90 detik per soal pada latihan per kategori', function (): void {
    expect(ModeLatihan::Tiu->komposisi())->toBe(['tiu' => 20])
        ->and(ModeLatihan::Tiu->durasiDetik(20))->toBe(1800);
});

it('menghitung ambang setara secara proporsional dan membulat ke atas', function (): void {
    $grade = PassingGrade::awal();

    expect($grade->setara(KategoriSoal::Twk, 30))->toBe(65)
        ->and($grade->setara(KategoriSoal::Tiu, 35))->toBe(80)
        ->and($grade->setara(KategoriSoal::Tkp, 45))->toBe(166)
        ->and($grade->setara(KategoriSoal::Twk, 15))->toBe(33)
        ->and($grade->setara(KategoriSoal::Tkp, 20))->toBe(74);
});
