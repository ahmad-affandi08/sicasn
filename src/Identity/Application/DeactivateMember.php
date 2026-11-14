<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\PenggunaTidakDitemukan;
use Src\Identity\Domain\Exception\TidakBolehMenonaktifkan;
use Src\Identity\Domain\Peran;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class DeactivateMember
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(int $penggunaId, int $aktorId, ?string $alasan): void
    {
        $pengguna = $this->pengguna->cari($penggunaId) ?? throw PenggunaTidakDitemukan::untuk($penggunaId);

        if ($pengguna->peran === Peran::Admin || $penggunaId === $aktorId) {
            throw TidakBolehMenonaktifkan::adminAtauDiriSendiri();
        }

        $alasan = $alasan !== null && trim($alasan) !== '' ? trim($alasan) : null;

        $this->pengguna->setAktif($penggunaId, false, $alasan, $this->clock->sekarang());
        $this->audit->catat($aktorId, 'pengguna.dinonaktifkan', $penggunaId, ['alasan' => $alasan]);
    }
}
