<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GantiPasswordRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Identity\Application\ChangeOwnPassword;
use Src\Identity\Domain\Exception\PasswordSalah;
use Src\Identity\Domain\Peran;

final class GantiPasswordController extends Controller
{
    public function tampil(Request $request): Response
    {
        return Inertia::render('GantiPassword', ['wajib' => $request->user()->wajib_ganti_password]);
    }

    public function proses(GantiPasswordRequest $request, ChangeOwnPassword $ganti): RedirectResponse
    {
        try {
            $ganti->handle((int) $request->user()->getAuthIdentifier(), (string) $request->input('password_lama'), (string) $request->input('password'));
        } catch (PasswordSalah $galat) {
            return back()->withErrors(['password_lama' => $galat->getMessage()]);
        }

        $tujuan = $request->user()->peran === Peran::Admin ? '/admin' : '/akun';

        return redirect($tujuan)->with('dialog', DialogFlash::sukses('Kata sandi diperbarui', 'Kata sandi baru Anda sudah tersimpan. Gunakan kata sandi ini saat masuk berikutnya.'));
    }
}
