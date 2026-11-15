<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure;

use Src\Formasi\Application\SumberReferensiWilayah;

final class BerkasReferensiWilayah implements SumberReferensiWilayah
{
    public function isiCsv(): string
    {
        return (string) file_get_contents(database_path('seeders/data/wilayah_kemendagri.csv'));
    }
}
