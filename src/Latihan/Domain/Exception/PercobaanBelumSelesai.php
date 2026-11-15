<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class PercobaanBelumSelesai extends DomainException
{
    public static function untuk(): self
    {
        return new self('Latihan ini belum selesai. Hasil dan pembahasan tampil setelah Anda menyelesaikannya.');
    }
}
