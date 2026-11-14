<?php

declare(strict_types=1);

namespace Src\Identity\Domain\Exception;

use DomainException;

final class EmailSudahTerdaftar extends DomainException
{
    public static function untuk(string $email): self
    {
        return new self("Email {$email} sudah terdaftar.");
    }
}
