<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilInfoPembayaranPublik;

final class BerandaController extends Controller
{
    public function __invoke(AmbilInfoPembayaranPublik $pembayaran): Response
    {
        return Inertia::render('Beranda', ['pembayaran' => $pembayaran->handle()]);
    }
}
