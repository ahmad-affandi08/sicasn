<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

final readonly class Lokasi
{
    private function __construct(
        public TipeLokasi $tipe,
        public string $nama,
        public string $teksAsli,
    ) {}

    public static function dari(?string $teks): self
    {
        $asli = NamaNormal::rapikan($teks);

        $pola = [
            [TipeLokasi::Kabupaten, '/^(?:kab\.?|kabupaten)\s+(.+)$/iu'],
            [TipeLokasi::Kota, '/^kota\s+(.+)$/iu'],
            [TipeLokasi::Provinsi, '/^(?:prov\.?|provinsi)\s+(.+)$/iu'],
        ];

        foreach ($pola as [$tipe, $ekspresi]) {
            if (preg_match($ekspresi, $asli, $cocok) === 1) {
                return new self($tipe, NamaNormal::rapikan($cocok[1]), $asli);
            }
        }

        return new self(TipeLokasi::SatuanKerja, $asli, $asli);
    }

    public function terpetakanKeWilayah(): bool
    {
        return $this->tipe !== TipeLokasi::SatuanKerja && $this->nama !== '';
    }
}
