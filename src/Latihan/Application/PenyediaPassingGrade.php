<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use Src\Latihan\Domain\PassingGrade;

interface PenyediaPassingGrade
{
    public function ambil(): PassingGrade;
}
