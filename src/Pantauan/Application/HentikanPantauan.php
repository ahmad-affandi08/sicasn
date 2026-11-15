<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

final readonly class HentikanPantauan
{
    public function __construct(private PantauanRepository $repository) {}

    public function handle(int $penggunaId, int $formasiId): void
    {
        $this->repository->hapus($penggunaId, $formasiId);
    }
}
