<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class PercobaanSudahSelesai extends DomainException
{
    public static function untuk(): self
    {
        return new self('Latihan ini sudah selesai atau waktunya habis, jawaban tidak bisa diubah lagi.');
    }
}
