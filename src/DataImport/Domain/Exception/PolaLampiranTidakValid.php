<?php

declare(strict_types=1);

namespace Src\DataImport\Domain\Exception;

use DomainException;

final class PolaLampiranTidakValid extends DomainException
{
    public static function bukanRegex(): self
    {
        return new self('Pola bukan ekspresi reguler yang valid.');
    }

    public static function tanpaGrupJabatan(): self
    {
        return new self('Pola harus memiliki grup bernama (?<jabatan>...). Grup (?<lokasi>...) bersifat opsional.');
    }
}
