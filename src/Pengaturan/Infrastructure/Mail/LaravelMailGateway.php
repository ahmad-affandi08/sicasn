<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Mail;

use Src\Pengaturan\Application\PengaturanEmailStore;
use Src\Shared\Application\HasilKirim;
use Src\Shared\Application\MailGateway;
use Src\Shared\Application\PesanEmail;
use Throwable;

final readonly class LaravelMailGateway implements MailGateway
{
    public function __construct(
        private PengaturanEmailStore $store,
        private PembangunMailer $pembangun,
    ) {}

    public function kirim(PesanEmail $pesan): HasilKirim
    {
        $pengaturan = $this->store->muat();

        if (! $pengaturan->terkonfigurasi()) {
            return HasilKirim::gagal('Penyedia email belum dikonfigurasi. Atur di Pengaturan, Email.', true);
        }

        $galat = [];

        foreach ($pengaturan->urutanPengirim() as [$penyedia, $konfigurasi]) {
            try {
                $this->pembangun
                    ->bangun($penyedia, $konfigurasi)
                    ->to($pesan->tujuan)
                    ->send(new PesanMarkdown($pesan, $pengaturan->pengirim));

                return HasilKirim::sukses($penyedia->value);
            } catch (Throwable $pengecualian) {
                $galat[] = $penyedia->label().': '.$this->ringkas($pengecualian);
            }
        }

        return HasilKirim::gagal(implode(' | ', $galat));
    }

    private function ringkas(Throwable $pengecualian): string
    {
        $pesan = preg_replace('/\s+/', ' ', $pengecualian->getMessage()) ?? '';

        return mb_substr(trim($pesan), 0, 250);
    }
}
