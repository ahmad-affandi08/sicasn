<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Src\Identity\Application\LogoutPengguna;

final class KeluarController extends Controller
{
    public function __invoke(LogoutPengguna $logout): RedirectResponse
    {
        $logout->handle();

        return redirect()->route('beranda');
    }
}
