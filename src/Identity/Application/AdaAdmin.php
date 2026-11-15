<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class AdaAdmin
{
    public function __construct(private PenggunaRepository $pengguna) {}

    public function handle(): bool
    {
        return $this->pengguna->adaAdmin();
    }
}
