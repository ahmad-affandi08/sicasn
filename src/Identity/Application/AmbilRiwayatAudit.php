<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Shared\Application\AuditLog;

final readonly class AmbilRiwayatAudit
{
    public function __construct(private AuditLog $audit) {}

    public function handle(int $penggunaId): array
    {
        return $this->audit->untukTarget($penggunaId);
    }
}
