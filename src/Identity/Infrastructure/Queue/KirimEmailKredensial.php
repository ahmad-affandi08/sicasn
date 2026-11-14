<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use RuntimeException;
use Src\Identity\Application\KredensialEmail;
use Src\Pengaturan\Application\AmbilTautanWhatsapp;
use Src\Shared\Application\LogEmail;
use Src\Shared\Application\MailGateway;
use Src\Shared\Application\PesanEmail;
use Throwable;

final class KirimEmailKredensial implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly int $logId,
        private readonly KredensialEmail $kredensial,
    ) {}

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(MailGateway $mail, LogEmail $log, AmbilTautanWhatsapp $whatsapp): void
    {
        $hasil = $mail->kirim(new PesanEmail(
            $this->kredensial->email,
            'Akun SICASN Anda sudah siap',
            'kredensial',
            [
                'nama' => $this->kredensial->nama,
                'email' => $this->kredensial->email,
                'password' => $this->kredensial->passwordSementara,
                'tautanLogin' => $this->kredensial->tautanLogin,
                'tautanWhatsapp' => $whatsapp->handle(),
                'masaBerlakuHari' => 7,
            ],
        ));

        if ($hasil->berhasil) {
            $log->berhasil($this->logId, (string) $hasil->penyedia);

            return;
        }

        if ($hasil->permanen) {
            $log->gagal($this->logId, (string) $hasil->galat);

            return;
        }

        throw new RuntimeException((string) $hasil->galat);
    }

    public function failed(Throwable $galat): void
    {
        resolve(LogEmail::class)->gagal($this->logId, mb_substr($galat->getMessage(), 0, 300));
    }
}
