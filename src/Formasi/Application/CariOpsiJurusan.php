<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class CariOpsiJurusan
{
    private const BATAS = 20;

    public function __construct(
        private OpsiFormasiRepository $opsi,
        private KebijakanFormasiAktif $kebijakan,
    ) {}

    public function handle(string $kataKunci): array
    {
        return $this->opsi->jurusan($kataKunci, $this->kebijakan->aktif(), self::BATAS);
    }
}
