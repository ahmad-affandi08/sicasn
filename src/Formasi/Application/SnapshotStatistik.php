<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;
use Src\Formasi\Domain\TipeInstansi;

final readonly class SnapshotStatistik
{
    public function __construct(
        public ?int $instansiId,
        public ?string $namaInstansi,
        public TipeInstansi $tipeInstansi,
        public string $tanggal,
        public StatusAngka $statusAngka,
        public ?int $formasi,
        public ?int $pelamar,
        public ?int $submit,
        public ?int $lolosAdministrasi,
        public TingkatData $tingkatData,
        public Keyakinan $keyakinan,
        public string $sumberNama,
        public ?string $sumberUrl,
        public ?string $catatan,
    ) {}
}
