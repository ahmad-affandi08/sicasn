<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

interface BknFormasiSource
{
    public function ambil(string $url): BarisBknSumber;
}
