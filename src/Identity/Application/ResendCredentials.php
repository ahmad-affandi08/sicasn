<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\PenggunaTidakDitemukan;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class ResendCredentials
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private PasswordGenerator $generator,
        private PasswordHasher $hasher,
        private PengirimKredensial $pengirim,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(int $penggunaId, int $aktorId, string $tautanLogin): void
    {
        $pengguna = $this->pengguna->cari($penggunaId) ?? throw PenggunaTidakDitemukan::untuk($penggunaId);

        $password = $this->generator->buat();
        $berlakuSampai = $this->clock->sekarang()->modify('+'.CreateMemberAccount::MASA_BERLAKU_PASSWORD_HARI.' days');

        $this->pengguna->setPasswordSementara($penggunaId, $this->hasher->hash($password), $berlakuSampai);
        $this->audit->catat($aktorId, 'kredensial.dikirim_ulang', $penggunaId);
        $this->pengirim->antrekan(new KredensialEmail($penggunaId, $pengguna->nama, $pengguna->email, $password, $tautanLogin));
    }
}
