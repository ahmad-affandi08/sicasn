<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\PenilaianPersaingan;
use Src\Formasi\Domain\StatusAngka;

final readonly class InstansiPeluang
{
    public function __construct(
        public int $instansiId,
        public string $nama,
        public string $tipe,
        public SnapshotStatistik $terbaru,
        public ?int $formasi,
        public ?string $formasiDipinjamDari,
        public PenilaianPersaingan $penilaian,
        public array $riwayat,
    ) {}

    public function golonganRasio(): int
    {
        return match (true) {
            $this->penilaian->rasio === null => 2,
            $this->penilaian->batasBawah => 1,
            default => 0,
        };
    }

    public function golonganPelamar(): int
    {
        return match (true) {
            $this->terbaru->pelamar === null && $this->terbaru->submit === null => 2,
            $this->terbaru->statusAngka === StatusAngka::Sementara || $this->terbaru->pelamar === null => 1,
            default => 0,
        };
    }

    public function pelamarDasar(): ?int
    {
        return $this->terbaru->pelamar ?? $this->terbaru->submit;
    }
}
