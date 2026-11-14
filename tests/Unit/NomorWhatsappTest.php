<?php

declare(strict_types=1);

use Src\Pengaturan\Domain\Exception\NomorWhatsappTidakValid;
use Src\Pengaturan\Domain\NomorWhatsapp;

it('menormalkan berbagai format nomor ke kode negara 62', function (string $masukan, string $diharapkan): void {
    expect(NomorWhatsapp::dari($masukan)->digit)->toBe($diharapkan);
})->with([
    'diawali nol' => ['0812-3456-7890', '6281234567890'],
    'diawali plus 62' => ['+62 812 3456 7890', '6281234567890'],
    'diawali 62' => ['6281234567890', '6281234567890'],
    'diawali 8' => ['81234567890', '6281234567890'],
]);

it('menolak nomor yang terlalu pendek atau terlalu panjang', function (string $masukan): void {
    NomorWhatsapp::dari($masukan);
})->with(['123', '1234567890123456789'])->throws(NomorWhatsappTidakValid::class);

it('membuat tautan wa.me dengan pesan ter-encode', function (): void {
    $tautan = NomorWhatsapp::dari('081234567890')->tautan('Halo Admin & tim');

    expect($tautan)->toBe('https://wa.me/6281234567890?text=Halo%20Admin%20%26%20tim');
});
