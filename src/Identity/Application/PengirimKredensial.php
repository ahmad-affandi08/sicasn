<?php

declare(strict_types=1);

namespace Src\Identity\Application;

interface PengirimKredensial
{
    public function antrekan(KredensialEmail $kredensial): void;
}
