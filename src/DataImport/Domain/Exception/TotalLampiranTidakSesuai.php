<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use DomainException;

final class TotalLampiranTidakSesuai extends DomainException
{
    public static function untuk(int $dihitung, int $resmi): self
    {
        return new self("Jumlah baris yang cocok ({$dihitung}) tidak sama dengan total resmi di pengumuman ({$resmi}). Perbaiki pola, jangan impor hasilnya.");
    }
}
