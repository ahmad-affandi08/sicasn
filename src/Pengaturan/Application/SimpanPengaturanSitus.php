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

    public function handle(bool $satuSesi, int $aktorId, ?string $url = null): void
    {
        $this->store->simpan(new PengaturanSitus($satuSesi, $url === null || trim($url) === '' ? null : rtrim(trim($url), '/')));
        $this->audit->catat($aktorId, 'pengaturan.situs_disimpan', null, ['satu_sesi' => $satuSesi, 'url' => $url]);
    }
}
