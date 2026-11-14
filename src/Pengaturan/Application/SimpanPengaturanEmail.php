<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use Src\Pengaturan\Domain\Enkripsi;
use Src\Pengaturan\Domain\Exception\PengaturanTidakValid;
use Src\Pengaturan\Domain\IdentitasPengirim;
use Src\Pengaturan\Domain\KonfigurasiSmtp;
use Src\Pengaturan\Domain\PengaturanEmail;
use Src\Pengaturan\Domain\PenyediaEmail;
use Src\Shared\Application\AuditLog;

final readonly class SimpanPengaturanEmail
{
    public function __construct(
        private PengaturanEmailStore $store,
        private AuditLog $audit,
    ) {}

    public function handle(InputPengaturanEmail $input, int $aktorId): void
    {
        $aktif = PenyediaEmail::tryFrom($input->aktif) ?? throw new PengaturanTidakValid('Penyedia email aktif tidak dikenal.');
        $cadangan = $input->cadangan === null || $input->cadangan === '' ? null : (PenyediaEmail::tryFrom($input->cadangan) ?? throw new PengaturanTidakValid('Penyedia cadangan tidak dikenal.'));

        if ($cadangan === $aktif) {
            throw new PengaturanTidakValid('Penyedia cadangan harus berbeda dari penyedia aktif.');
        }

        $pengirim = new IdentitasPengirim(trim($input->pengirimNama), trim($input->pengirimAlamat), $this->kosongJadiNull($input->alamatBalas));

        if (! $pengirim->valid()) {
            throw new PengaturanTidakValid('Nama dan alamat email pengirim wajib diisi dengan benar.');
        }

        $lama = $this->store->muat();
        $konfigurasi = $lama->konfigurasi;

        foreach ($input->konfigurasi as $kode => $masukan) {
            $penyedia = PenyediaEmail::tryFrom((string) $kode);

            if ($penyedia === null || ! in_array($penyedia, [$aktif, $cadangan], true)) {
                continue;
            }

            $enkripsi = Enkripsi::tryFrom($masukan->enkripsi) ?? throw new PengaturanTidakValid('Jenis enkripsi tidak dikenal.');
            $passwordLama = $konfigurasi[$penyedia->value]->password ?? '';
            $password = $masukan->password !== null && $masukan->password !== '' ? $masukan->password : $passwordLama;

            $konfigurasi[$penyedia->value] = new KonfigurasiSmtp(trim($masukan->host), $masukan->port, $enkripsi, trim($masukan->username), $password);
        }

        foreach ([$aktif, $cadangan] as $penyedia) {
            if ($penyedia !== null && ! isset($konfigurasi[$penyedia->value])) {
                throw new PengaturanTidakValid("Konfigurasi {$penyedia->label()} belum diisi.");
            }

            if ($penyedia !== null && $konfigurasi[$penyedia->value]->lengkap() === false) {
                throw new PengaturanTidakValid("Konfigurasi {$penyedia->label()} belum lengkap.");
            }
        }

        $this->store->simpan(new PengaturanEmail($aktif, $cadangan, $pengirim, $konfigurasi));
        $this->audit->catat($aktorId, 'pengaturan.email_disimpan', null, ['aktif' => $aktif->value, 'cadangan' => $cadangan?->value]);
    }

    private function kosongJadiNull(?string $nilai): ?string
    {
        return $nilai === null || trim($nilai) === '' ? null : trim($nilai);
    }
}
