<?php

declare(strict_types=1);

namespace Src\Latihan\Domain\Exception;

use DomainException;

final class JawabanTidakValid extends DomainException
{
    public static function untuk(): self
    {
        return new self('Jawaban tidak sesuai dengan soal ini.');
    }
}
