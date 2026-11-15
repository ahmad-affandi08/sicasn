<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class OpsiFormasi
{
    public function __construct(
        public array $jenjang,
        public array $provinsi,
        public array $kabupatenKota,
        public array $jenisFormasi,
        public ?OpsiPilihan $instansiTerpilih,
        public array $jurusanTerpilih,
    ) {}
}
