<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Shared\Application\AuditLog;
use Src\Shared\Application\HasilKirim;
use Src\Shared\Application\MailGateway;
use Src\Shared\Application\PesanEmail;

final readonly class KirimEmailUji
{
    public function __construct(
        private MailGateway $mail,
        private AuditLog $audit,
    ) {}

    public function handle(string $tujuan, int $aktorId): HasilKirim
    {
        $hasil = $this->mail->kirim(new PesanEmail($tujuan, 'Email uji SICASN', 'uji', ['tujuan' => $tujuan]));

        $this->audit->catat($aktorId, 'pengaturan.email_diuji', null, [
            'berhasil' => $hasil->berhasil,
            'penyedia' => $hasil->penyedia,
        ]);

        return $hasil;
    }
}
