<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\EmailSudahTerdaftar;
use Src\Identity\Domain\Peran;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class CreateAdminAccount
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private PasswordHasher $hasher,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(string $nama, string $email, string $password): int
    {
        $email = mb_strtolower(trim($email));

        if ($this->pengguna->emailTerpakai($email)) {
            throw EmailSudahTerdaftar::untuk($email);
        }

        $id = $this->pengguna->simpanBaru(
            new PenggunaBaru($nama, $email, null, null, '', null),
            Peran::Admin,
            $this->hasher->hash($password),
            false,
            null,
            $this->clock->sekarang(),
        );

        $this->audit->catat(null, 'admin.dibuat', $id, ['email' => $email]);

        return $id;
    }
}
