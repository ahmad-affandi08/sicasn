<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MasukRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Identity\Application\LoginPengguna;
use Src\Identity\Domain\HasilLogin;
use Src\Identity\Domain\Peran;
use Src\Pengaturan\Application\AmbilTautanWhatsapp;

final class MasukController extends Controller
{
    public function tampil(): Response
    {
        return Inertia::render('Masuk');
    }

    public function proses(MasukRequest $request, LoginPengguna $login, AmbilTautanWhatsapp $whatsapp): RedirectResponse
    {
        $request->pastikanTidakTerbatasi();

        $hasil = $login->handle((string) $request->input('email'), (string) $request->input('password'), $request->boolean('ingat'));

        if ($hasil === HasilLogin::Berhasil) {
            $request->bersihkanBatas();

            return redirect()->intended($request->user()->peran === Peran::Admin ? '/admin' : '/peluang-instansi');
        }

        $request->catatGagal();

        return match ($hasil) {
            HasilLogin::AkunNonaktif => back()->with('dialog', DialogFlash::peringatan('Akun Anda sedang nonaktif', 'Akun ini dinonaktifkan oleh admin. Hubungi admin untuk mengaktifkannya kembali.', $whatsapp->handle())),
            HasilLogin::PasswordSementaraKedaluwarsa => back()->with('dialog', DialogFlash::peringatan('Kata sandi sementara kedaluwarsa', 'Kata sandi sementara hanya berlaku 7 hari. Hubungi admin untuk dikirimi yang baru.', $whatsapp->handle())),
            default => back()->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])->onlyInput('email'),
        };
    }
}
