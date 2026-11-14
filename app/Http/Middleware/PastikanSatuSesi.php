<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\DialogFlash;
use Closure;
use Illuminate\Http\Request;
use Src\Identity\Application\LogoutPengguna;
use Src\Identity\Domain\Peran;
use Src\Pengaturan\Application\AmbilPengaturanSitus;
use Symfony\Component\HttpFoundation\Response;

final readonly class PastikanSatuSesi
{
    public function __construct(
        private LogoutPengguna $logout,
        private AmbilPengaturanSitus $situs,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        if ($pengguna !== null && $pengguna->peran === Peran::Member && $this->situs->handle()->satuSesi
            && $pengguna->sesi_aktif_id !== $request->session()->getId()) {
            $this->logout->handle();

            return redirect()->route('masuk')->with('dialog', DialogFlash::info(
                'Akun dipakai di perangkat lain',
                'Akun Anda sedang masuk di perangkat lain, jadi sesi ini diakhiri. Masuk kembali bila ingin memakainya di sini.',
            ));
        }

        return $next($request);
    }
}
