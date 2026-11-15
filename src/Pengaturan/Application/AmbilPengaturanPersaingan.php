<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Formasi\Domain\AmbangPersaingan;

final readonly class AmbilPengaturanPersaingan
{
    public function __construct(private PengaturanPersainganStore $store) {}

    public function handle(): AmbangPersaingan
    {
        return $this->store->muat();
    }
}
