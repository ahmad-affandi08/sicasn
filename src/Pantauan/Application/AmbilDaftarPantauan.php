<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use Src\Formasi\Application\ItemFormasi;

final readonly class AmbilDaftarPantauan
{
    public function __construct(
        private PantauanRepository $repository,
        private SumberFormasi $formasi,
    ) {}

    public function handle(int $penggunaId): array
    {
        $daftar = $this->repository->daftar($penggunaId);
        $peta = [];

        foreach ($this->formasi->item(array_map(fn (array $baris) => $baris['formasiId'], $daftar)) as $item) {
            $peta[$item->formasi->id] = $item;
        }

        $hasil = [];

        foreach ($daftar as $baris) {
            $item = $peta[$baris['formasiId']] ?? null;

            if ($item instanceof ItemFormasi) {
                $hasil[] = new ItemPantauan($item, $baris['catatan'], $baris['ditambahkanAt']);
            }
        }

        return $hasil;
    }
}
