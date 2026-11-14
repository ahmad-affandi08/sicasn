<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure;

use Src\Identity\Application\PasswordGenerator;

final class SecurePasswordGenerator implements PasswordGenerator
{
    private const HURUF_KECIL = 'abcdefghijkmnpqrstuvwxyz';

    private const HURUF_BESAR = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

    private const ANGKA = '23456789';

    private const SIMBOL = '@#$%+=?';

    private const PANJANG = 14;

    public function buat(): string
    {
        $semua = self::HURUF_KECIL.self::HURUF_BESAR.self::ANGKA.self::SIMBOL;

        $karakter = [
            $this->ambil(self::HURUF_KECIL),
            $this->ambil(self::HURUF_BESAR),
            $this->ambil(self::ANGKA),
            $this->ambil(self::SIMBOL),
        ];

        while (count($karakter) < self::PANJANG) {
            $karakter[] = $this->ambil($semua);
        }

        for ($i = count($karakter) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$karakter[$i], $karakter[$j]] = [$karakter[$j], $karakter[$i]];
        }

        return implode('', $karakter);
    }

    private function ambil(string $himpunan): string
    {
        return $himpunan[random_int(0, strlen($himpunan) - 1)];
    }
}
