<?php

declare(strict_types=1);

use Src\Pengaturan\Domain\NomorWhatsapp;
use Src\Pengaturan\Domain\PengaturanWhatsapp;

it('mengganti penanda nama admin pada templat pesan', function (): void {
    $pengaturan = new PengaturanWhatsapp(NomorWhatsapp::dari('081234567890'), 'Budi', 'Halo {nama_admin}, mau daftar.');

    expect($pengaturan->pesan())->toBe('Halo Budi, mau daftar.');
});

it('memakai nama Admin dan templat awal bila kosong', function (): void {
    $pengaturan = new PengaturanWhatsapp(null, '', '');

    expect($pengaturan->pesan())->toBe('Halo Admin, saya ingin berlangganan SICASN.');
});

it('tidak memberi tautan bila nomor belum diatur', function (): void {
    expect((new PengaturanWhatsapp(null, '', ''))->tautan())->toBeNull();
});
