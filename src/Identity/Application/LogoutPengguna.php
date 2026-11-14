<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class LogoutPengguna
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(): void
    {
        $this->authenticator->keluar();
    }
}
