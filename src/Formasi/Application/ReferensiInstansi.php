<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

interface ReferensiInstansi
{
    public function pastikan(array $namaInstansi): array;
}
