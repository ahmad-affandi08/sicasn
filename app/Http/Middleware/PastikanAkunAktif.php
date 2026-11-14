<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\DialogFlash;
use Closure;
use Illuminate\Http\Request;
use Src\Identity\Application\LogoutPengguna;
use Src\Pengaturan\Application\AmbilTautanWhatsapp;
use Symfony\Component\HttpFoundation\Response;

final readonly class PastikanAkunAktif
{
    public function __construct(
        private LogoutPengguna $logout,
        private AmbilTautanWhatsapp $tautanWhatsapp,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->aktif === false) {
            $this->logout->handle();

            return redirect()->route('masuk')->with('dialog', DialogFlash::peringatan(
                'Akun Anda sedang nonaktif',
                'Akun ini dinonaktifkan oleh admin. Hubungi admin untuk mengaktifkannya kembali.',
                $this->tautanWhatsapp->handle(),
            ));
        }

        return $next($request);
    }
}
