<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class InputKonfigurasiSmtp
{
    public function __construct(
        public string $host,
        public int $port,
        public string $enkripsi,
        public string $username,
        public ?string $password,
    ) {}
}
