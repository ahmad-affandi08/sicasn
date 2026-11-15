<?php

declare(strict_types=1);

namespace Src\DataImport\Domain;

enum StatusBatch: string
{
    case Berjalan = 'berjalan';
    case Sukses = 'sukses';
    case Sebagian = 'sebagian';
    case Gagal = 'gagal';

    public function berhasil(): bool
    {
        return $this === self::Sukses || $this === self::Sebagian;
    }
}
