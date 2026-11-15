<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class CariOpsiInstansi
{
    private const BATAS = 20;

    public function __construct(
        private OpsiFormasiRepository $opsi,
        private KebijakanFormasiAktif $kebijakan,
    ) {}

    public function handle(string $kataKunci): array
    {
        return $this->opsi->instansi($kataKunci, $this->kebijakan->aktif(), self::BATAS);
    }
}
