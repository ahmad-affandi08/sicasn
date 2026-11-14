<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PengaturanPembayaran
{
    public function __construct(
        public array $rekening,
        public string $hargaTeks,
        public string $catatan,
        public bool $tampilkanRekeningPublik,
    ) {}

    public function terisi(): bool
    {
        return $this->rekening !== [] && $this->hargaTeks !== '';
    }
}
