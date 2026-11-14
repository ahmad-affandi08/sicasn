<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\PasswordSalah;
use Src\Identity\Domain\Exception\PenggunaTidakDitemukan;
use Src\Shared\Application\AuditLog;

final readonly class ChangeOwnPassword
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private PasswordHasher $hasher,
        private AuditLog $audit,
    ) {}

    public function handle(int $penggunaId, string $passwordLama, string $passwordBaru): void
    {
        $hashLama = $this->pengguna->ambilHashPassword($penggunaId) ?? throw PenggunaTidakDitemukan::untuk($penggunaId);

        if (! $this->hasher->cocok($passwordLama, $hashLama)) {
            throw new PasswordSalah('Kata sandi saat ini tidak cocok.');
        }

        $this->pengguna->setPasswordBaru($penggunaId, $this->hasher->hash($passwordBaru));
        $this->audit->catat($penggunaId, 'password.diganti', $penggunaId);
    }
}
