<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class AmbilKabupatenKota
{
    public function __construct(
        private OpsiFormasiRepository $opsi,
        private KebijakanFormasiAktif $kebijakan,
    ) {}

    public function handle(int $provinsiId): array
    {
        return $this->opsi->kabupatenKota($provinsiId, $this->kebijakan->aktif());
    }
}
