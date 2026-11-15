<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

interface PemicuSinkron
{
    public function antrekan(?int $aktorId): void;
}
