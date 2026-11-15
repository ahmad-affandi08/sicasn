<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

interface PencarianFormasi
{
    public function ringkas(array $ids): array;

    public function cari(FilterFormasi $filter, ?array $instansiDiizinkan, ?array $instansiDikecualikan): HalamanFormasi;
}
