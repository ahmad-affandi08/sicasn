<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

final readonly class AmbilStatusPantauan
{
    public function __construct(private PantauanRepository $repository) {}

    public function handle(int $penggunaId, array $formasiIds): array
    {
        return $this->repository->idDipantau($penggunaId, $formasiIds);
    }
}
