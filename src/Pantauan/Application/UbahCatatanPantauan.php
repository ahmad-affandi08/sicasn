<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use Src\Pantauan\Domain\BatasPantauan;
use Src\Pantauan\Domain\Exception\CatatanTerlaluPanjang;
use Src\Pantauan\Domain\Exception\FormasiTidakDitemukan;
use Src\Shared\Application\Clock;

final readonly class UbahCatatanPantauan
{
    public function __construct(
        private PantauanRepository $repository,
        private Clock $clock,
    ) {}

    public function handle(int $penggunaId, int $formasiId, ?string $catatan): void
    {
        $bersih = $catatan === null ? '' : trim(preg_replace('/[ \t]+/u', ' ', $catatan) ?? '');

        if (mb_strlen($bersih) > BatasPantauan::MAKS_CATATAN) {
            throw CatatanTerlaluPanjang::untuk();
        }

        if (! $this->repository->ubahCatatan($penggunaId, $formasiId, $bersih === '' ? null : $bersih, $this->clock->sekarang())) {
            throw FormasiTidakDitemukan::untuk();
        }
    }
}
