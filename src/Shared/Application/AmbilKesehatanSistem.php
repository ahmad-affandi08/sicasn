<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class AmbilKesehatanSistem
{
    private const BATAS_DETAK_DETIK = 300;

    private const JENDELA_EMAIL = '-7 days';

    public function __construct(
        private PembacaKesehatanSistem $pembaca,
        private Clock $jam,
    ) {}

    public function handle(): KesehatanSistem
    {
        $sekarang = $this->jam->sekarang();
        $detak = $this->pembaca->detakTerakhir();

        return new KesehatanSistem(
            $this->pembaca->antreanMenunggu(),
            $this->pembaca->antreanGagal(),
            $this->pembaca->emailGagalSejak($sekarang->modify(self::JENDELA_EMAIL)),
            $detak,
            $detak !== null && $sekarang->getTimestamp() - $detak->getTimestamp() <= self::BATAS_DETAK_DETIK,
        );
    }
}
