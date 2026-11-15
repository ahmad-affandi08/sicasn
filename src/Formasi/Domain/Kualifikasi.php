<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final readonly class Kualifikasi
{
    public function __construct(
        public string $jenjang,
        public ?string $jurusan,
        public bool $semuaJurusan,
    ) {}

    public function kunci(): string
    {
        return $this->jenjang.'|'.($this->jurusan ?? '*');
    }
}
