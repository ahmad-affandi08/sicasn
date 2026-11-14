<?php

declare(strict_types=1);

namespace Src\Identity\Domain;

enum HasilLogin
{
    case Berhasil;
    case KredensialSalah;
    case AkunNonaktif;
    case PasswordSementaraKedaluwarsa;
}
