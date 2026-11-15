<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PengaturanSitus
{
    public function __construct(
        public bool $satuSesi,
        public ?string $url = null,
    ) {}

    public function tautanMasuk(): ?string
    {
        return $this->url === null ? null : rtrim($this->url, '/').'/masuk';
    }
}
