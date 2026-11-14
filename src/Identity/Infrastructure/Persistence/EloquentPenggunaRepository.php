<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Src\Identity\Application\HalamanPengguna;
use Src\Identity\Application\PenggunaBaru;
use Src\Identity\Application\PenggunaDto;
use Src\Identity\Application\PenggunaRepository;
use Src\Identity\Application\RingkasanPengguna;
use Src\Identity\Domain\Peran;
use Src\Shared\Infrastructure\Persistence\EmailLogModel;

final class EloquentPenggunaRepository implements PenggunaRepository
{
    public function simpanBaru(
        PenggunaBaru $data,
        Peran $peran,
        string $passwordHash,
        bool $wajibGantiPassword,
        ?DateTimeImmutable $berlakuSampai,
        DateTimeImmutable $sekarang,
    ): int {
        $pengguna = new UserModel;
        $pengguna->forceFill([
            'name' => $data->nama,
            'email' => $data->email,
            'password' => $passwordHash,
            'peran' => $peran,
            'no_whatsapp' => $data->noWhatsapp,
            'aktif' => true,
            'diaktifkan_at' => $sekarang,
            'catatan_pembayaran' => $data->catatanPembayaran,
            'wajib_ganti_password' => $wajibGantiPassword,
            'password_sementara_berlaku_sampai' => $berlakuSampai,
            'dibuat_oleh' => $data->dibuatOleh,
        ])->save();

        return (int) $pengguna->getKey();
    }

    public function cari(int $id): ?PenggunaDto
    {
        $model = $this->kueriDenganStatusEmail()->whereKey($id)->first();

        return $model === null ? null : $this->petakan($model);
    }

    public function emailTerpakai(string $email): bool
    {
        return UserModel::query()->where('email', $email)->exists();
    }

    public function ambilHashPassword(int $id): ?string
    {
        return UserModel::query()->whereKey($id)->value('password');
    }

    public function setPasswordSementara(int $id, string $passwordHash, DateTimeImmutable $berlakuSampai): void
    {
        UserModel::query()->whereKey($id)->first()?->forceFill([
            'password' => $passwordHash,
            'wajib_ganti_password' => true,
            'password_sementara_berlaku_sampai' => $berlakuSampai,
            'sesi_aktif_id' => null,
        ])->save();
    }

    public function setPasswordBaru(int $id, string $passwordHash): void
    {
        UserModel::query()->whereKey($id)->first()?->forceFill([
            'password' => $passwordHash,
            'wajib_ganti_password' => false,
            'password_sementara_berlaku_sampai' => null,
        ])->save();
    }

    public function setAktif(int $id, bool $aktif, ?string $alasan, DateTimeImmutable $sekarang): void
    {
        $pengguna = UserModel::query()->whereKey($id)->first();

        if ($pengguna === null) {
            return;
        }

        $pengguna->forceFill($aktif
            ? ['aktif' => true, 'diaktifkan_at' => $sekarang, 'alasan_nonaktif' => null]
            : ['aktif' => false, 'dinonaktifkan_at' => $sekarang, 'alasan_nonaktif' => $alasan, 'sesi_aktif_id' => null]
        )->save();
    }

    public function halaman(?string $cari, ?bool $aktif, int $halaman, int $perHalaman): HalamanPengguna
    {
        $kueri = $this->kueriDenganStatusEmail()
            ->when($cari !== null, fn (Builder $q) => $q->where(function (Builder $dalam) use ($cari): void {
                $dalam->where('name', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%")
                    ->orWhere('no_whatsapp', 'like', "%{$cari}%");
            }))
            ->when($aktif !== null, fn (Builder $q) => $q->where('aktif', $aktif))
            ->orderByDesc('id');

        $paginasi = $kueri->paginate($perHalaman, ['*'], 'halaman', $halaman);

        return new HalamanPengguna(
            $paginasi->getCollection()->map(fn (UserModel $m) => $this->petakan($m))->all(),
            $paginasi->total(),
            $paginasi->currentPage(),
            $paginasi->perPage(),
            $paginasi->lastPage(),
        );
    }

    public function ringkasan(): RingkasanPengguna
    {
        $aktif = UserModel::query()->where('peran', Peran::Member->value)->where('aktif', true)->count();
        $nonaktif = UserModel::query()->where('peran', Peran::Member->value)->where('aktif', false)->count();

        return new RingkasanPengguna($aktif, $nonaktif);
    }

    private function kueriDenganStatusEmail(): Builder
    {
        $terbaru = fn (string $kolom) => EmailLogModel::query()
            ->select($kolom)
            ->whereColumn('user_id', 'users.id')
            ->where('tipe', 'kredensial')
            ->orderByDesc('id')
            ->limit(1);

        return UserModel::query()->addSelect([
            'users.*',
            'status_email' => $terbaru('status'),
            'galat_email' => $terbaru('error'),
        ]);
    }

    private function petakan(UserModel $model): PenggunaDto
    {
        return new PenggunaDto(
            (int) $model->getKey(),
            $model->name,
            $model->email,
            $model->no_whatsapp,
            $model->peran,
            $model->aktif,
            $model->catatan_pembayaran,
            $model->alasan_nonaktif,
            $model->wajib_ganti_password,
            $model->created_at?->toIso8601String() ?? '',
            $model->last_login_at?->toIso8601String(),
            $model->getAttribute('status_email'),
            $model->getAttribute('galat_email'),
        );
    }
}
