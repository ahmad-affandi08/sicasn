<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Src\Pengaturan\Application\AmbilTautanWhatsapp;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly AmbilTautanWhatsapp $tautanWhatsapp) {}

    public function share(Request $request): array
    {
        $pengguna = $request->user();

        return [
            ...parent::share($request),
            'aplikasi' => ['nama' => config('app.name')],
            'whatsappUrl' => fn (): ?string => $this->tautanWhatsapp->handle(),
            'auth' => [
                'pengguna' => $pengguna === null ? null : [
                    'nama' => $pengguna->name,
                    'email' => $pengguna->email,
                    'peran' => $pengguna->peran->value,
                    'wajibGantiPassword' => $pengguna->wajib_ganti_password,
                ],
            ],
            'dialog' => fn (): ?array => $request->session()->get('dialog'),
        ];
    }
}
