<?php

declare(strict_types=1);

namespace Src\Pantauan\Domain\Exception;

use DomainException;
use Src\Pantauan\Domain\BatasPantauan;

final class PerbandinganTidakValid extends DomainException
{
    public static function jumlahSalah(): self
    {
        return new self('Pilih '.BatasPantauan::MIN_PERBANDINGAN.' sampai '.BatasPantauan::MAKS_PERBANDINGAN.' formasi dari daftar pantauan untuk dibandingkan.');
    }
}
