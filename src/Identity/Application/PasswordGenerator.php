<?php

declare(strict_types=1);

namespace Src\Identity\Application;

interface PasswordGenerator
{
    public function buat(): string;
}
