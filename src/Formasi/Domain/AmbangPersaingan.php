<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

use InvalidArgumentException;

final readonly class AmbangPersaingan
{
    public const LONGGAR_MAKS_AWAL = 5.0;

    public const KETAT_MIN_AWAL = 20.0;

    public function __construct(
        public float $longgarMaks,
        public float $ketatMin,
    ) {
        if ($longgarMaks <= 0 || $ketatMin <= $longgarMaks || $ketatMin > 10000) {
            throw new InvalidArgumentException('Ambang persaingan tidak valid: batas Longgar harus di atas 0 dan lebih kecil dari batas Ketat.');
        }
    }

    public static function awal(): self
    {
        return new self(self::LONGGAR_MAKS_AWAL, self::KETAT_MIN_AWAL);
    }
}
