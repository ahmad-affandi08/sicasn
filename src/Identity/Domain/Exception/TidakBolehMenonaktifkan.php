<?php

declare(strict_types=1);

namespace Src\Identity\Domain\Exception;

use DomainException;

final class TidakBolehMenonaktifkan extends DomainException
{
    public static function adminAtauDiriSendiri(): self
    {
        return new self('Akun admin dan akun Anda sendiri tidak dapat dinonaktifkan.');
    }
}
