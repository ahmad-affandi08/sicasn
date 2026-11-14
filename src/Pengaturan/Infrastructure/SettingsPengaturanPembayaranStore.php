<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use Src\Pengaturan\Application\PengaturanPembayaranStore;
use Src\Pengaturan\Application\SettingsRepository;
use Src\Pengaturan\Domain\PengaturanPembayaran;
use Src\Pengaturan\Domain\Rekening;

final readonly class SettingsPengaturanPembayaranStore implements PengaturanPembayaranStore
{
    private const GRUP = 'pembayaran';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): PengaturanPembayaran
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);
        $data = json_decode((string) ($nilai['rekening'] ?? '[]'), true);

        $rekening = array_map(
            fn (array $baris): Rekening => new Rekening((string) ($baris['bank'] ?? ''), (string) ($baris['nomor'] ?? ''), (string) ($baris['atas_nama'] ?? '')),
            is_array($data) ? array_values(array_filter($data, 'is_array')) : [],
        );

        return new PengaturanPembayaran(
            $rekening,
            (string) ($nilai['harga_teks'] ?? ''),
            (string) ($nilai['catatan'] ?? ''),
            ($nilai['tampilkan_rekening_publik'] ?? '0') === '1',
        );
    }

    public function simpan(PengaturanPembayaran $pengaturan): void
    {
        $this->settings->simpanBanyak(self::GRUP, [
            'rekening' => json_encode(array_map(
                fn (Rekening $r): array => ['bank' => $r->bank, 'nomor' => $r->nomor, 'atas_nama' => $r->atasNama],
                $pengaturan->rekening,
            ), JSON_THROW_ON_ERROR),
            'harga_teks' => $pengaturan->hargaTeks,
            'catatan' => $pengaturan->catatan,
            'tampilkan_rekening_publik' => $pengaturan->tampilkanRekeningPublik ? '1' : '0',
        ]);
    }
}
