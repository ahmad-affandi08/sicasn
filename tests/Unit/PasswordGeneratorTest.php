<?php

declare(strict_types=1);

use Src\Identity\Infrastructure\SecurePasswordGenerator;

it('membuat kata sandi 14 karakter dengan huruf besar, kecil, angka, dan simbol', function (): void {
    $password = (new SecurePasswordGenerator)->buat();

    expect($password)->toHaveLength(14)
        ->and($password)->toMatch('/[a-z]/')
        ->and($password)->toMatch('/[A-Z]/')
        ->and($password)->toMatch('/[0-9]/')
        ->and($password)->toMatch('/[@#$%+=?]/');
});

it('tidak memakai karakter yang merusak tampilan markdown atau HTML pada email', function (): void {
    $generator = new SecurePasswordGenerator;

    for ($i = 0; $i < 200; $i++) {
        expect($generator->buat())->not->toMatch('/[&<>"\'`*_\[\]\\\\~|]/');
    }
});

it('menghasilkan nilai yang berbeda tiap kali', function (): void {
    $generator = new SecurePasswordGenerator;
    $nilai = array_map(fn () => $generator->buat(), range(1, 50));

    expect(array_unique($nilai))->toHaveCount(50);
});
