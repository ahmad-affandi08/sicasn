<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum Cakupan: string
{
    case Nasional = 'nasional';
    case Instansi = 'instansi';
    case Jabatan = 'jabatan';
}
