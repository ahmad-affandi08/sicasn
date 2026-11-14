<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class IdentitasPengirim
{
    public function __construct(
        public string $nama,
        public string $alamat,
        public ?string $alamatBalas,
    ) {}

    public function valid(): bool
    {
        return $this->nama !== '' && filter_var($this->alamat, FILTER_VALIDATE_EMAIL) !== false;
    }
}
