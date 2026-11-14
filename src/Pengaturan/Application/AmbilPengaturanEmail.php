<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class AmbilPengaturanEmail
{
    public function __construct(private PengaturanEmailStore $store) {}

    public function handle(): TampilanPengaturanEmail
    {
        $pengaturan = $this->store->muat();

        $konfigurasi = [];

        foreach ($pengaturan->konfigurasi as $penyedia => $smtp) {
            $konfigurasi[$penyedia] = [
                'host' => $smtp->host,
                'port' => $smtp->port,
                'enkripsi' => $smtp->enkripsi->value,
                'username' => $smtp->username,
                'passwordTersimpan' => $smtp->password !== '',
            ];
        }

        return new TampilanPengaturanEmail(
            $pengaturan->aktif?->value,
            $pengaturan->cadangan?->value,
            $pengaturan->pengirim->nama,
            $pengaturan->pengirim->alamat,
            $pengaturan->pengirim->alamatBalas ?? '',
            $konfigurasi,
            $pengaturan->terkonfigurasi(),
        );
    }
}
