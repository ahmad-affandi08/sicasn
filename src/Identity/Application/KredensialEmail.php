<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class KredensialEmail
{
    public function __construct(
        public int $penggunaId,
        public string $nama,
        public string $email,
        public string $passwordSementara,
        public string $tautanLogin,
    ) {}
}
