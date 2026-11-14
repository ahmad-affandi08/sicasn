<?php

declare(strict_types=1);

use Src\Pengaturan\Domain\Enkripsi;
use Src\Pengaturan\Domain\IdentitasPengirim;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PengaturanEmail;
use Src\Pengaturan\Domain\PenyediaEmail;

function smtp(string $password = 'x'): KonfigurasiSmtp
{
    return new KonfigurasiSmtp('smtp.contoh.id', 465, Enkripsi::Ssl, 'u@contoh.id', $password);
}

it('menyediakan preset untuk setiap penyedia dengan port yang valid', function (PenyediaEmail $penyedia): void {
    $preset = $penyedia->preset();

    expect($preset->port)->toBeGreaterThan(0)->toBeLessThanOrEqual(65535)
        ->and($penyedia->label())->not->toBe('')
        ->and($preset->petunjuk)->not->toBe('');

    if ($penyedia !== PenyediaEmail::SmtpKustom) {
        expect($preset->host)->not->toBe('');
    }
})->with(PenyediaEmail::cases());

it('memakai preset Hostinger yang benar', function (): void {
    $preset = PenyediaEmail::Hostinger->preset();

    expect($preset->host)->toBe('smtp.hostinger.com')
        ->and($preset->port)->toBe(465)
        ->and($preset->enkripsi)->toBe(Enkripsi::Ssl);
});

it('memetakan enkripsi ke skema smtp yang tepat', function (): void {
    expect(Enkripsi::Ssl->skema())->toBe('smtps')
        ->and(Enkripsi::Tls->skema())->toBe('smtp')
        ->and(Enkripsi::Tanpa->skema())->toBe('smtp');
});

it('baru dianggap terkonfigurasi bila penyedia aktif, konfigurasi lengkap, dan pengirim valid', function (): void {
    $pengirim = new IdentitasPengirim('SICASN', 'noreply@contoh.id', null);

    expect((new PengaturanEmail(null, null, $pengirim, []))->terkonfigurasi())->toBeFalse()
        ->and((new PengaturanEmail(PenyediaEmail::Hostinger, null, $pengirim, []))->terkonfigurasi())->toBeFalse()
        ->and((new PengaturanEmail(PenyediaEmail::Hostinger, null, $pengirim, ['hostinger' => smtp('')]))->terkonfigurasi())->toBeFalse()
        ->and((new PengaturanEmail(PenyediaEmail::Hostinger, null, new IdentitasPengirim('SICASN', 'salah', null), ['hostinger' => smtp()]))->terkonfigurasi())->toBeFalse()
        ->and((new PengaturanEmail(PenyediaEmail::Hostinger, null, $pengirim, ['hostinger' => smtp()]))->terkonfigurasi())->toBeTrue();
});

it('mengurutkan pengirim: aktif dahulu lalu cadangan, melewati yang tidak lengkap', function (): void {
    $pengirim = new IdentitasPengirim('SICASN', 'noreply@contoh.id', null);
    $pengaturan = new PengaturanEmail(PenyediaEmail::Hostinger, PenyediaEmail::Gmail, $pengirim, [
        'hostinger' => smtp(),
        'gmail' => smtp(),
    ]);

    $urutan = array_map(fn (array $item) => $item[0], $pengaturan->urutanPengirim());

    expect($urutan)->toBe([PenyediaEmail::Hostinger, PenyediaEmail::Gmail]);

    $tanpaCadangan = new PengaturanEmail(PenyediaEmail::Hostinger, PenyediaEmail::Gmail, $pengirim, ['hostinger' => smtp(), 'gmail' => smtp('')]);

    expect($tanpaCadangan->urutanPengirim())->toHaveCount(1);
});
