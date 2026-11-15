<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

interface BarisBknSumber
{
    public function hash(): string;

    public function baris(): iterable;

    public function tutup(): void;
}
