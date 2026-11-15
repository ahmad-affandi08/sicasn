<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

interface DetailFormasiRepository
{
    public function cari(int $id): ?RincianFormasi;
}
