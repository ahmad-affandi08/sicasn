<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AkunController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $pengguna = $request->user();

        return Inertia::render('Akun', [
            'profil' => ['nama' => $pengguna->name, 'email' => $pengguna->email, 'noWhatsapp' => $pengguna->no_whatsapp],
        ]);
    }
}
