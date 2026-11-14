<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanSitus;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanSitus
{
    public function __construct(
        private PengaturanSitusStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(bool $satuSesi, int $aktorId): void
    {
        $this->store->simpan(new PengaturanSitus($satuSesi));
        $this->audit->catat($aktorId, 'pengaturan.situs_disimpan', null, ['satu_sesi' => $satuSesi]);
    }
}
