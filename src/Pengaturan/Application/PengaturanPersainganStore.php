<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Formasi\Domain\AmbangPersaingan;

interface PengaturanPersainganStore
{
    public function muat(): AmbangPersaingan;

    public function simpan(AmbangPersaingan $ambang): void;
}
