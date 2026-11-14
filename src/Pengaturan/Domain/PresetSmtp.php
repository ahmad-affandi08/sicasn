<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PresetSmtp
{
    public function __construct(
        public string $host,
        public int $port,
        public Enkripsi $enkripsi,
        public string $username,
        public string $petunjuk,
    ) {}
}
