<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\HasilLogin;

final readonly class LoginPengguna
{
    public function __construct(private Authenticator $authenticator) {}

    public function handle(string $email, string $password, bool $ingat): HasilLogin
    {
        return $this->authenticator->coba(mb_strtolower(trim($email)), $password, $ingat);
    }
}
