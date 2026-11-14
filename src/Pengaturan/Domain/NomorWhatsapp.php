<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

use Src\Pengaturan\Domain\Exception\NomorWhatsappTidakValid;

final readonly class NomorWhatsapp
{
    private function __construct(public string $digit) {}

    public static function dari(string $masukan): self
    {
        $digit = preg_replace('/\D+/', '', $masukan) ?? '';

        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }

        if (str_starts_with($digit, '8')) {
            $digit = '62'.$digit;
        }

        if (strlen($digit) < 9 || strlen($digit) > 15) {
            throw new NomorWhatsappTidakValid('Nomor WhatsApp harus 9 sampai 15 digit.');
        }

        return new self($digit);
    }

    public function tautan(string $pesan): string
    {
        return 'https://wa.me/'.$this->digit.'?text='.rawurlencode($pesan);
    }
}
