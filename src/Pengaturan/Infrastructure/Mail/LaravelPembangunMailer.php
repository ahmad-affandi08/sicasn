<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Mail;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PenyediaEmail;

final class LaravelPembangunMailer implements PembangunMailer
{
    public function bangun(PenyediaEmail $penyedia, KonfigurasiSmtp $konfigurasi): Mailer
    {
        return Mail::build([
            'transport' => 'smtp',
            'scheme' => $konfigurasi->enkripsi->skema(),
            'host' => $konfigurasi->host,
            'port' => $konfigurasi->port,
            'username' => $konfigurasi->username,
            'password' => $konfigurasi->password,
            'timeout' => 30,
        ]);
    }
}
