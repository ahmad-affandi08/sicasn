<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

final readonly class PeluangMentah
{
    public function __construct(
        public int $instansiId,
        public ?int $pelamar,
        public ?int $submit,
        public ?int $formasi,
        public ?string $formasiDariTanggal,
        public StatusAngka $statusAngka,
        public TingkatData $tingkatData,
        public Keyakinan $keyakinan,
        public string $tanggalSnapshot,
        public string $sumberNama,
        public ?string $sumberUrl,
    ) {}
}
