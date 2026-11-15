<?php

declare(strict_types=1);

namespace Src\Latihan\Domain;

final readonly class PassingGrade
{
    public const TWK_AWAL = 65;

    public const TIU_AWAL = 80;

    public const TKP_AWAL = 166;

    public function __construct(
        public int $twk,
        public int $tiu,
        public int $tkp,
    ) {}

    public static function awal(): self
    {
        return new self(self::TWK_AWAL, self::TIU_AWAL, self::TKP_AWAL);
    }

    public function resmi(KategoriSoal $kategori): int
    {
        return match ($kategori) {
            KategoriSoal::Twk => $this->twk,
            KategoriSoal::Tiu => $this->tiu,
            KategoriSoal::Tkp => $this->tkp,
        };
    }

    public function setara(KategoriSoal $kategori, int $jumlahSoal): int
    {
        return (int) ceil($this->resmi($kategori) * $jumlahSoal / $kategori->jumlahResmi());
    }
}
