<?php

declare(strict_types=1);

namespace Src\Pantauan\Infrastructure;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Pantauan\Application\PantauanRepository;

final class EloquentPantauanRepository implements PantauanRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function tambah(int $penggunaId, int $formasiId, DateTimeImmutable $sekarang): bool
    {
        $waktu = $sekarang->format(self::FORMAT);

        return DB::table('pantauans')->insertOrIgnore(['user_id' => $penggunaId, 'formasi_id' => $formasiId, 'created_at' => $waktu, 'updated_at' => $waktu]) > 0;
    }

    public function hapus(int $penggunaId, int $formasiId): bool
    {
        return DB::table('pantauans')->where('user_id', $penggunaId)->where('formasi_id', $formasiId)->delete() > 0;
    }

    public function ubahCatatan(int $penggunaId, int $formasiId, ?string $catatan, DateTimeImmutable $sekarang): bool
    {
        return DB::table('pantauans')->where('user_id', $penggunaId)->where('formasi_id', $formasiId)->update(['catatan' => $catatan, 'updated_at' => $sekarang->format(self::FORMAT)]) > 0
            || DB::table('pantauans')->where('user_id', $penggunaId)->where('formasi_id', $formasiId)->exists();
    }

    public function jumlah(int $penggunaId): int
    {
        return DB::table('pantauans')->where('user_id', $penggunaId)->count();
    }

    public function daftar(int $penggunaId): array
    {
        return DB::table('pantauans')->where('user_id', $penggunaId)->orderByDesc('created_at')->orderByDesc('id')->get(['formasi_id', 'catatan', 'created_at'])
            ->map(fn (object $b) => ['formasiId' => (int) $b->formasi_id, 'catatan' => $b->catatan, 'ditambahkanAt' => (string) $b->created_at])
            ->all();
    }

    public function idDipantau(int $penggunaId, array $formasiIds): array
    {
        if ($formasiIds === []) {
            return [];
        }

        return DB::table('pantauans')->where('user_id', $penggunaId)->whereIn('formasi_id', $formasiIds)->pluck('formasi_id')->map(fn ($id) => (int) $id)->all();
    }
}
