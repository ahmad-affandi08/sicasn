<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use Src\Pantauan\Domain\BatasPantauan;
use Src\Pantauan\Domain\Exception\BatasPantauanTercapai;
use Src\Pantauan\Domain\Exception\FormasiTidakDitemukan;
use Src\Shared\Application\Clock;

final readonly class PantauFormasi
{
    public function __construct(
        private PantauanRepository $repository,
        private SumberFormasi $formasi,
        private Clock $clock,
    ) {}

    public function handle(int $penggunaId, int $formasiId): void
    {
        if ($this->formasi->item([$formasiId]) === []) {
            throw FormasiTidakDitemukan::untuk();
        }

        if ($this->repository->idDipantau($penggunaId, [$formasiId]) !== []) {
            return;
        }

        if ($this->repository->jumlah($penggunaId) >= BatasPantauan::MAKS_PANTAUAN) {
            throw BatasPantauanTercapai::untukPengguna();
        }

        $this->repository->tambah($penggunaId, $formasiId, $this->clock->sekarang());
    }
}
