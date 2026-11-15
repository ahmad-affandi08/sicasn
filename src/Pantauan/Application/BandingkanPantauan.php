<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use Src\Pantauan\Domain\BatasPantauan;
use Src\Pantauan\Domain\Exception\PerbandinganTidakValid;

final readonly class BandingkanPantauan
{
    public function __construct(private AmbilDaftarPantauan $daftar) {}

    public function handle(int $penggunaId, array $formasiIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $formasiIds)));

        if (count($ids) < BatasPantauan::MIN_PERBANDINGAN || count($ids) > BatasPantauan::MAKS_PERBANDINGAN) {
            throw PerbandinganTidakValid::jumlahSalah();
        }

        $terpilih = array_values(array_filter($this->daftar->handle($penggunaId), fn (ItemPantauan $p) => in_array($p->item->formasi->id, $ids, true)));

        if (count($terpilih) !== count($ids)) {
            throw PerbandinganTidakValid::jumlahSalah();
        }

        usort($terpilih, fn (ItemPantauan $a, ItemPantauan $b) => array_search($a->item->formasi->id, $ids, true) <=> array_search($b->item->formasi->id, $ids, true));

        return $terpilih;
    }
}
