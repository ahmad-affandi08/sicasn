<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use Src\Shared\Application\Clock;
use Src\Shared\Application\LogEmail;
use Src\Shared\Infrastructure\Persistence\EmailLogModel;

final readonly class EloquentLogEmail implements LogEmail
{
    public function __construct(private Clock $clock) {}

    public function mulai(?int $penggunaId, string $tipe): int
    {
        return (int) EmailLogModel::query()->create([
            'user_id' => $penggunaId,
            'tipe' => $tipe,
            'status' => 'menunggu',
        ])->getKey();
    }

    public function berhasil(int $id, string $penyedia): void
    {
        EmailLogModel::query()->whereKey($id)->update([
            'status' => 'terkirim',
            'penyedia' => $penyedia,
            'error' => null,
            'dikirim_at' => $this->clock->sekarang(),
        ]);
    }

    public function gagal(int $id, string $galat): void
    {
        EmailLogModel::query()->whereKey($id)->update([
            'status' => 'gagal',
            'error' => mb_substr($galat, 0, 500),
        ]);
    }
}
