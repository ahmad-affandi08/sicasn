<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PenyediaEmail;
use Src\Pengaturan\Infrastructure\Mail\PembangunMailer;

final class PembangunMailerPalsu implements PembangunMailer
{
    public array $dibangunUntuk = [];

    public function __construct(private readonly array $gagalUntuk = []) {}

    public function bangun(PenyediaEmail $penyedia, KonfigurasiSmtp $konfigurasi): Mailer
    {
        $this->dibangunUntuk[] = $penyedia;

        if (in_array($penyedia, $this->gagalUntuk, true)) {
            return Mail::build([
                'transport' => 'smtp',
                'scheme' => 'smtp',
                'host' => '127.0.0.1',
                'port' => 1,
                'timeout' => 2,
            ]);
        }

        return Mail::mailer('array');
    }

    public static function pesanTerkirim(): array
    {
        return Mail::mailer('array')->getSymfonyTransport()->messages()->all();
    }
}
