<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Identity\Application\PenggunaDto;

final class PenggunaResource
{
    public static function dari(PenggunaDto $pengguna): array
    {
        return [
            'id' => $pengguna->id,
            'nama' => $pengguna->nama,
            'email' => $pengguna->email,
            'noWhatsapp' => $pengguna->noWhatsapp,
            'peran' => $pengguna->peran->value,
            'aktif' => $pengguna->aktif,
            'catatanPembayaran' => $pengguna->catatanPembayaran,
            'alasanNonaktif' => $pengguna->alasanNonaktif,
            'wajibGantiPassword' => $pengguna->wajibGantiPassword,
            'dibuatAt' => $pengguna->dibuatAt,
            'lastLoginAt' => $pengguna->lastLoginAt,
            'statusEmail' => $pengguna->statusEmail,
            'galatEmail' => $pengguna->galatEmail,
        ];
    }
}
