<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final class NamaNormal
{
    public static function dari(?string $nama): string
    {
        $bersih = preg_replace('/\s+/u', ' ', trim((string) $nama)) ?? '';

        return mb_strtoupper($bersih);
    }

    public static function rapikan(?string $nama): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $nama)) ?? '';
    }
}
