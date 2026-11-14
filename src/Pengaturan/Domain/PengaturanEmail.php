<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class PengaturanEmail
{
    public function __construct(
        public ?PenyediaEmail $aktif,
        public ?PenyediaEmail $cadangan,
        public IdentitasPengirim $pengirim,
        public array $konfigurasi,
    ) {}

    public function konfigurasiUntuk(PenyediaEmail $penyedia): ?KonfigurasiSmtp
    {
        return $this->konfigurasi[$penyedia->value] ?? null;
    }

    public function terkonfigurasi(): bool
    {
        if ($this->aktif === null || ! $this->pengirim->valid()) {
            return false;
        }

        return $this->konfigurasiUntuk($this->aktif)?->lengkap() === true;
    }

    public function urutanPengirim(): array
    {
        $urutan = [];

        foreach ([$this->aktif, $this->cadangan] as $penyedia) {
            $konfigurasi = $penyedia === null ? null : $this->konfigurasiUntuk($penyedia);

            if ($penyedia !== null && $konfigurasi?->lengkap() === true) {
                $urutan[] = [$penyedia, $konfigurasi];
            }
        }

        return $urutan;
    }
}
