<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

interface PenyimpananPratinjau
{
    public function simpan(DataPratinjau $data): string;

    public function ambil(string $token): ?DataPratinjau;

    public function hapus(string $token): void;
}
