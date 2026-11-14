<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Exception\PenggunaTidakDitemukan;

final readonly class AmbilPengguna
{
    public function __construct(private PenggunaRepository $pengguna) {}

    public function handle(int $id): PenggunaDto
    {
        return $this->pengguna->cari($id) ?? throw PenggunaTidakDitemukan::untuk($id);
    }
}
