<?php

declare(strict_types=1);

namespace Src\Shared\Application;

interface LogEmail
{
    public function mulai(?int $penggunaId, string $tipe): int;

    public function berhasil(int $id, string $penyedia): void;

    public function gagal(int $id, string $galat): void;
}
