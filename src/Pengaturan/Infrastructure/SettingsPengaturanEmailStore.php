<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure;

use Src\Pengaturan\Application\PengaturanEmailStore;
use Src\Pengaturan\Application\SettingsRepository;
use Src\Pengaturan\Domain\IdentitasPengirim;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PengaturanEmail;
use Src\Pengaturan\Domain\PenyediaEmail;

final readonly class SettingsPengaturanEmailStore implements PengaturanEmailStore
{
    private const GRUP = 'email';

    private const AWALAN_SMTP = 'smtp.';

    public function __construct(private SettingsRepository $settings) {}

    public function muat(): PengaturanEmail
    {
        $nilai = $this->settings->ambilGrup(self::GRUP);
        $konfigurasi = [];

        foreach ($nilai as $kunci => $isi) {
            if (! str_starts_with($kunci, self::AWALAN_SMTP) || $isi === null) {
                continue;
            }

            $data = json_decode($isi, true);

            if (is_array($data)) {
                $konfigurasi[substr($kunci, strlen(self::AWALAN_SMTP))] = KonfigurasiSmtp::dariArray($data);
            }
        }

        return new PengaturanEmail(
            PenyediaEmail::tryFrom((string) ($nilai['aktif'] ?? '')),
            PenyediaEmail::tryFrom((string) ($nilai['cadangan'] ?? '')),
            new IdentitasPengirim((string) ($nilai['pengirim_nama'] ?? 'SICASN'), (string) ($nilai['pengirim_alamat'] ?? ''), $nilai['alamat_balas'] ?? null),
            $konfigurasi,
        );
    }

    public function simpan(PengaturanEmail $pengaturan): void
    {
        $nilai = [
            'aktif' => $pengaturan->aktif?->value,
            'cadangan' => $pengaturan->cadangan?->value,
            'pengirim_nama' => $pengaturan->pengirim->nama,
            'pengirim_alamat' => $pengaturan->pengirim->alamat,
            'alamat_balas' => $pengaturan->pengirim->alamatBalas,
        ];

        $rahasia = [];

        foreach ($pengaturan->konfigurasi as $kode => $smtp) {
            $kunci = self::AWALAN_SMTP.$kode;
            $nilai[$kunci] = json_encode($smtp->keArray(), JSON_THROW_ON_ERROR);
            $rahasia[] = $kunci;
        }

        $this->settings->simpanBanyak(self::GRUP, $nilai, $rahasia);
    }
}
