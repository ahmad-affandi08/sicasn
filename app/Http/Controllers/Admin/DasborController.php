<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Identity\Application\AmbilRingkasanPengguna;
use Src\Pengaturan\Application\AmbilKesiapan;

final class DasborController extends Controller
{
    public function __invoke(AmbilRingkasanPengguna $ringkasan, AmbilKesiapan $kesiapan): Response
    {
        $pengguna = $ringkasan->handle();
        $siap = $kesiapan->handle();

        return Inertia::render('Admin/Dasbor', [
            'ringkasan' => ['aktif' => $pengguna->aktif, 'nonaktif' => $pengguna->nonaktif],
            'kesiapan' => ['email' => $siap->email, 'whatsapp' => $siap->whatsapp, 'pembayaran' => $siap->pembayaran],
        ]);
    }
}
