<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Mail;

use Illuminate\Contracts\Mail\Mailer;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PenyediaEmail;

interface PembangunMailer
{
    public function bangun(PenyediaEmail $penyedia, KonfigurasiSmtp $konfigurasi): Mailer;
}
