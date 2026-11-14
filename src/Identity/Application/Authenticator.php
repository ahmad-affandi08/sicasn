<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\HasilLogin;

interface Authenticator
{
    public function coba(string $email, string $password, bool $ingat): HasilLogin;

    public function keluar(): void;
}
