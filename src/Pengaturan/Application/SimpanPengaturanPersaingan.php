<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use InvalidArgumentException;
use Src\Formasi\Domain\AmbangPersaingan;
use Src\Pengaturan\Domain\Exception\PengaturanTidakValid;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanPersaingan
{
    public function __construct(
        private PengaturanPersainganStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(float $longgarMaks, float $ketatMin, int $aktorId): void
    {
        try {
            $ambang = new AmbangPersaingan($longgarMaks, $ketatMin);
        } catch (InvalidArgumentException $galat) {
            throw new PengaturanTidakValid($galat->getMessage());
        }

        $this->store->simpan($ambang);
        $this->audit->catat($aktorId, 'pengaturan.persaingan_disimpan', null, ['longgar_maks' => $longgarMaks, 'ketat_min' => $ketatMin]);
    }
}
