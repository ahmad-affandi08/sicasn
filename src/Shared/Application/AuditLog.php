<?php

declare(strict_types=1);

namespace Src\Shared\Application;

interface AuditLog
{
    public function catat(?int $aktorId, string $aksi, ?int $targetId, array $rincian = []): void;

    public function untukTarget(int $targetId, int $batas = 20): array;
}
