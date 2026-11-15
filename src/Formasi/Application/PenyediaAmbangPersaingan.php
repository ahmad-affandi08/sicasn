<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;

interface PenyediaAmbangPersaingan
{
    public function ambil(): AmbangPersaingan;
}
