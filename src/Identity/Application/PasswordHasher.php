<?php

declare(strict_types=1);

namespace Src\Identity\Application;

interface PasswordHasher
{
    public function hash(string $password): string;

    public function cocok(string $password, string $hash): bool;
}
