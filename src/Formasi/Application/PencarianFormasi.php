<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

interface PencarianFormasi
{
    public function cari(FilterFormasi $filter, ?array $instansiDiizinkan, ?array $instansiDikecualikan): HalamanFormasi;
}
