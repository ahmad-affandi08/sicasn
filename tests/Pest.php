<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\InputKonfigurasiSmtp;
use Src\Pengaturan\Application\InputPengaturanEmail;
use Src\Pengaturan\Application\SimpanPengaturanEmail;
use Src\Pengaturan\Infrastructure\Mail\PembangunMailer;
use Tests\Support\PembangunMailerPalsu;
use Tests\TestCase;

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->withoutVite();
        config([
            'queue.default' => 'sync',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'mail.default' => 'array',
            'hashing.bcrypt.rounds' => 4,
        ]);
    })
    ->in('Feature');

function masukSebagai(TestCase $uji, UserModel $pengguna, string $password = 'password'): void
{
    $uji->post('/masuk', ['email' => $pengguna->email, 'password' => $password])->assertSessionHasNoErrors();
    $uji->withCookie(config('session.cookie'), session()->getId());
}

function pasangMailerPalsu(array $gagalUntuk = []): PembangunMailerPalsu
{
    $palsu = new PembangunMailerPalsu($gagalUntuk);
    app()->instance(PembangunMailer::class, $palsu);

    return $palsu;
}

function konfigurasiEmailTersimpan(string $aktif = 'hostinger', ?string $cadangan = null): void
{
    $smtp = fn (string $host) => new InputKonfigurasiSmtp($host, 465, 'ssl', 'noreply@contoh.id', 'rahasia-smtp-123');

    $konfigurasi = [$aktif => $smtp('smtp.contoh.id')];

    if ($cadangan !== null) {
        $konfigurasi[$cadangan] = $smtp('smtp.cadangan.id');
    }

    app(SimpanPengaturanEmail::class)->handle(
        new InputPengaturanEmail($aktif, $cadangan, 'SICASN', 'noreply@contoh.id', null, $konfigurasi),
        1,
    );
}
