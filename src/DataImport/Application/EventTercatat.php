<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class EventTercatat
{
    public function __construct(
        public string $eventId,
        public string $namaEvent,
        public string $jenisPengadaan,
        public int $jumlahBaris,
        public bool $diabaikan,
        public string $pertamaKaliTerlihat,
        public bool $sudahDiberitahukan,
    ) {}
}
