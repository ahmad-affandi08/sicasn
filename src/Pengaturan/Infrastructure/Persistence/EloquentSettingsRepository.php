<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Persistence;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Src\Pengaturan\Application\SettingsRepository;

final class EloquentSettingsRepository implements SettingsRepository
{
    public function ambilGrup(string $grup): array
    {
        $baris = Cache::rememberForever($this->kunciCache($grup), fn (): array => PengaturanModel::query()
            ->where('grup', $grup)
            ->get()
            ->mapWithKeys(fn (PengaturanModel $m): array => [$m->kunci => ['nilai' => $m->nilai, 'terenkripsi' => $m->terenkripsi]])
            ->all());

        $hasil = [];

        foreach ($baris as $kunci => $isi) {
            $hasil[$kunci] = $isi['terenkripsi'] && $isi['nilai'] !== null ? Crypt::decryptString($isi['nilai']) : $isi['nilai'];
        }

        return $hasil;
    }

    public function simpanBanyak(string $grup, array $nilai, array $kunciRahasia = []): void
    {
        foreach ($nilai as $kunci => $isi) {
            $rahasia = in_array($kunci, $kunciRahasia, true);

            PengaturanModel::query()->updateOrCreate(
                ['grup' => $grup, 'kunci' => $kunci],
                ['nilai' => $isi !== null && $rahasia ? Crypt::encryptString($isi) : $isi, 'terenkripsi' => $rahasia],
            );
        }

        Cache::forget($this->kunciCache($grup));
    }

    private function kunciCache(string $grup): string
    {
        return "pengaturan.{$grup}";
    }
}
