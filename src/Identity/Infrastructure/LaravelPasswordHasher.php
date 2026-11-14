<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure;

use Illuminate\Support\Facades\Hash;
use Src\Identity\Application\PasswordHasher;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function hash(string $password): string
    {
        return Hash::make($password);
    }

    public function cocok(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }
}
