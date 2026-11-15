<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class TanggapiGalat
{
    private const KODE_RAMAH = [403, 404, 419, 429, 500, 503];

    public function __invoke(Response $respons, Throwable $galat, Request $request): Response
    {
        $kode = $respons->getStatusCode();

        if ($request->expectsJson() || ! in_array($kode, self::KODE_RAMAH, true) || ($kode >= 500 && app()->hasDebugModeEnabled())) {
            return $respons;
        }

        if ($kode === 419 || $this->tindakanDariHalaman($request)) {
            return back()->with('dialog', $this->dialog($kode));
        }

        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            Inertia::share(['aplikasi' => ['nama' => config('app.name')], 'whatsappUrl' => null, 'auth' => ['pengguna' => null], 'panduan' => false, 'dialog' => null]);
        }

        return Inertia::render('Galat', ['kode' => $kode])->toResponse($request)->setStatusCode($kode);
    }

    private function tindakanDariHalaman(Request $request): bool
    {
        return $request->header('X-Inertia') !== null && ! $request->isMethod('GET');
    }

    private function dialog(int $kode): array
    {
        return match ($kode) {
            403 => DialogFlash::gagal('Tindakan tidak diizinkan', 'Akun Anda tidak memiliki izin untuk tindakan ini.'),
            404 => DialogFlash::gagal('Data tidak ditemukan', 'Data yang Anda tuju tidak ditemukan atau sudah dihapus.'),
            419 => DialogFlash::peringatan('Sesi halaman habis', 'Halaman terlalu lama terbuka. Muat ulang halaman lalu coba lagi.'),
            429 => DialogFlash::peringatan('Terlalu banyak permintaan', 'Anda terlalu sering mengirim permintaan. Tunggu sebentar lalu coba lagi.'),
            default => DialogFlash::galat('Terjadi gangguan di server', 'Kami sedang mengalami kendala. Coba lagi beberapa saat; bila berlanjut, hubungi admin.'),
        };
    }
}
