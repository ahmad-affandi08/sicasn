<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Src\Shared\Application\PembacaKesehatanSistem;

final class DatabasePembacaKesehatanSistem implements PembacaKesehatanSistem
{
    private const KUNCI_DETAK = 'sicasn.detak_scheduler';

    public function antreanMenunggu(): int
    {
        return DB::table('jobs')->count();
    }

    public function antreanGagal(): int
    {
        return DB::table('failed_jobs')->count();
    }

    public function emailGagalSejak(DateTimeImmutable $sejak): int
    {
        return DB::table('email_logs')->where('status', 'gagal')->where('created_at', '>=', $sejak)->count();
    }

    public function detakTerakhir(): ?DateTimeImmutable
    {
        $nilai = Cache::get(self::KUNCI_DETAK);

        return is_int($nilai) ? (new DateTimeImmutable)->setTimestamp($nilai) : null;
    }

    public function catatDetak(DateTimeImmutable $sekarang): void
    {
        Cache::forever(self::KUNCI_DETAK, $sekarang->getTimestamp());
    }
}
