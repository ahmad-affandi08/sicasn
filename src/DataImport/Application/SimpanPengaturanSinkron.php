<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\PengaturanSinkronTidakValid;
use Src\DataImport\Domain\FrekuensiSinkron;
use Src\DataImport\Domain\PengaturanSinkron;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanSinkron
{
    public function __construct(
        private PengaturanSinkronStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(string $url, string $frekuensi, int $aktorId): void
    {
        $url = trim($url);
        $bagian = parse_url($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false || ($bagian['scheme'] ?? '') !== 'https' || empty($bagian['host'])) {
            throw new PengaturanSinkronTidakValid('Alamat sumber data harus berupa URL https yang valid.');
        }

        $jadwal = FrekuensiSinkron::tryFrom($frekuensi) ?? throw new PengaturanSinkronTidakValid('Frekuensi sinkron tidak dikenal.');

        $this->store->simpan(new PengaturanSinkron($url, $jadwal));
        $this->audit->catat($aktorId, 'pengaturan.sinkron_disimpan', null, ['frekuensi' => $jadwal->value, 'host' => $bagian['host']]);
    }
}
