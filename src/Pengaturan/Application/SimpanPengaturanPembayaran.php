<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\PengaturanPembayaran;
use Src\Pengaturan\Domain\Rekening;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanPembayaran
{
    public function __construct(
        private PengaturanPembayaranStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(array $rekening, string $hargaTeks, string $catatan, bool $tampilkanRekeningPublik, int $aktorId): void
    {
        $daftar = [];

        foreach ($rekening as $baris) {
            $bank = trim((string) ($baris['bank'] ?? ''));
            $nomor = trim((string) ($baris['nomor'] ?? ''));
            $atasNama = trim((string) ($baris['atas_nama'] ?? ''));

            if ($bank !== '' && $nomor !== '') {
                $daftar[] = new Rekening($bank, $nomor, $atasNama);
            }
        }

        $this->store->simpan(new PengaturanPembayaran($daftar, trim($hargaTeks), trim($catatan), $tampilkanRekeningPublik));
        $this->audit->catat($aktorId, 'pengaturan.pembayaran_disimpan', null);
    }
}
