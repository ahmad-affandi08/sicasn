<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class EventTeramati
{
    public function __construct(
        public string $eventId,
        public string $namaEvent,
        public array $jenisPengadaan,
        public int $jumlahBaris,
        public bool $diabaikan,
    ) {}
}
