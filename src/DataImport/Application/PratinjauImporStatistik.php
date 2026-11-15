<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\Shared\Application\Clock;

final readonly class PratinjauImporStatistik
{
    public function __construct(
        private PenyimpananPratinjau $penyimpanan,
        private Clock $clock,
    ) {}

    public function handle(string $isiCsv, string $namaBerkas): PratinjauImpor
    {
        $hasil = PenguraiCsvStatistik::urai($isiCsv, $this->clock->sekarang());
        $data = new DataPratinjau($namaBerkas, $hasil->baris, $hasil->galat, $hasil->totalBaris);

        if ($hasil->galatBerkas !== null || $hasil->baris === []) {
            return PetaPratinjau::dari(null, $data, $hasil->galatBerkas ?? 'Tidak ada baris yang valid untuk diimpor.');
        }

        return PetaPratinjau::dari($this->penyimpanan->simpan($data), $data);
    }
}
