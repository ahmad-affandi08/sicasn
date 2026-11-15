<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\PengaturanSinkron;

final readonly class StatusSinkron
{
    public function __construct(
        public array $riwayat,
        public ?RingkasBatch $terakhirBerhasil,
        public bool $sedangBerjalan,
        public array $event,
        public PengaturanSinkron $pengaturan,
        public array $jenisAktif,
        public int $formasiAktif,
    ) {}
}
