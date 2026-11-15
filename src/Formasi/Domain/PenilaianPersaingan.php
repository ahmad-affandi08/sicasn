<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final readonly class PenilaianPersaingan
{
    public function __construct(
        public TingkatPersaingan $tingkat,
        public ?float $rasio,
        public bool $batasBawah,
    ) {}
}
