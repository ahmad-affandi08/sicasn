<?php

declare(strict_types=1);

namespace Src\Pantauan\Domain\Exception;

use DomainException;
use Src\Pantauan\Domain\BatasPantauan;

final class BatasPantauanTercapai extends DomainException
{
    public static function untukPengguna(): self
    {
        return new self('Daftar pantauan penuh ('.BatasPantauan::MAKS_PANTAUAN.' formasi). Hentikan pantauan yang sudah tidak diperlukan, lalu coba lagi.');
    }
}
