<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Shared\Application\Clock;

final readonly class TandaiPanduanDilihat
{
    public function __construct(
        private PenggunaRepository $pengguna,
        private Clock $jam,
    ) {}

    public function handle(int $penggunaId): void
    {
        $this->pengguna->tandaiPanduanDilihat($penggunaId, $this->jam->sekarang());
    }
}
