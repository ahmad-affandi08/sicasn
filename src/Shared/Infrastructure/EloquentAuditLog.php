<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use Illuminate\Support\Facades\DB;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\CatatanAudit;
use Src\Shared\Infrastructure\Persistence\AuditLogModel;

final class EloquentAuditLog implements AuditLog
{
    public function catat(?int $aktorId, string $aksi, ?int $targetId, array $rincian = []): void
    {
        AuditLogModel::query()->create([
            'aktor_id' => $aktorId,
            'aksi' => $aksi,
            'target_id' => $targetId,
            'rincian' => $rincian === [] ? null : $rincian,
        ]);
    }

    public function untukTarget(int $targetId, int $batas = 20): array
    {
        $catatan = AuditLogModel::query()->where('target_id', $targetId)->orderByDesc('id')->limit($batas)->get();
        $nama = DB::table('users')->whereIn('id', $catatan->pluck('aktor_id')->filter()->unique()->all())->pluck('name', 'id');

        return $catatan->map(fn (AuditLogModel $c) => new CatatanAudit(
            $c->aksi,
            $c->aktor_id === null ? null : ($nama[$c->aktor_id] ?? null),
            $c->rincian ?? [],
            $c->created_at?->toIso8601String() ?? '',
        ))->all();
    }
}
