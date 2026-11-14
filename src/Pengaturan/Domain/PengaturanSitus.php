<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PengaturanSitus
{
    public function __construct(public bool $satuSesi) {}
}
