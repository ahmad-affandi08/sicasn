<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class CatatDetakScheduler
{
    public function __construct(
        private PembacaKesehatanSistem $pembaca,
        private Clock $jam,
    ) {}

    public function handle(): void
    {
        $this->pembaca->catatDetak($this->jam->sekarang());
    }
}
