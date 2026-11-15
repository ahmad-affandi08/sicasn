<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use RuntimeException;

final class SumberBknGagal extends RuntimeException
{
    public static function diblokir(int $status): self
    {
        return new self("BKN menolak permintaan (HTTP {$status}). Sinkron dihentikan dan akan dicoba lagi pada jadwal berikutnya.");
    }

    public static function tidakTersedia(string $sebab): self
    {
        return new self("Sumber data BKN tidak dapat diambil: {$sebab}");
    }

    public static function skemaTidakDikenal(string $sebab): self
    {
        return new self("Bentuk respons BKN tidak dikenal: {$sebab}. Data lama tetap dipakai.");
    }
}
