<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\PenggunaTidakDitemukan;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class ActivateMember
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(int $penggunaId, int $aktorId): void
    {
        $this->pengguna->cari($penggunaId) ?? throw PenggunaTidakDitemukan::untuk($penggunaId);

        $this->pengguna->setAktif($penggunaId, true, null, $this->clock->sekarang());
        $this->audit->catat($aktorId, 'pengguna.diaktifkan', $penggunaId);
    }
}
