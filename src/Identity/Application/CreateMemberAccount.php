<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\EmailSudahTerdaftar;
use Src\Identity\Domain\Peran;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class CreateMemberAccount
{
    public const MASA_BERLAKU_PASSWORD_HARI = 7;

    public function __construct(
        private PenggunaRepository $pengguna,
        private PasswordGenerator $generator,
        private PasswordHasher $hasher,
        private PengirimKredensial $pengirim,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(PenggunaBaru $data): int
    {
        $email = mb_strtolower(trim($data->email));

        if ($this->pengguna->emailTerpakai($email)) {
            throw EmailSudahTerdaftar::untuk($email);
        }

        $sekarang = $this->clock->sekarang();
        $password = $this->generator->buat();

        $id = $this->pengguna->simpanBaru(
            new PenggunaBaru($data->nama, $email, $data->noWhatsapp, $data->catatanPembayaran, $data->tautanLogin, $data->dibuatOleh),
            Peran::Member,
            $this->hasher->hash($password),
            true,
            $sekarang->modify('+'.self::MASA_BERLAKU_PASSWORD_HARI.' days'),
            $sekarang,
        );

        $this->audit->catat($data->dibuatOleh, 'pengguna.dibuat', $id, ['email' => $email]);
        $this->pengirim->antrekan(new KredensialEmail($id, $data->nama, $email, $password, $data->tautanLogin));

        return $id;
    }
}
