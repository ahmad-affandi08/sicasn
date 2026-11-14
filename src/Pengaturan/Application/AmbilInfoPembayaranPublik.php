<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class AmbilInfoPembayaranPublik
{
    public function __construct(private PengaturanPembayaranStore $store) {}

    public function handle(): ?array
    {
        $pengaturan = $this->store->muat();

        if ($pengaturan->hargaTeks === '' && $pengaturan->catatan === '') {
            return null;
        }

        return [
            'hargaTeks' => $pengaturan->hargaTeks,
            'catatan' => $pengaturan->catatan,
            'rekening' => $pengaturan->tampilkanRekeningPublik
                ? array_map(fn ($r) => ['bank' => $r->bank, 'nomor' => $r->nomor, 'atasNama' => $r->atasNama], $pengaturan->rekening)
                : [],
        ];
    }
}
