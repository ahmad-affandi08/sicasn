<?php

use App\Http\Middleware\AmankanHeader;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PastikanAdmin;
use App\Http\Middleware\PastikanAkunAktif;
use App\Http\Middleware\PastikanPasswordSudahDiganti;
use App\Http\Middleware\PastikanSatuSesi;
use App\Http\Middleware\SesiAmanBilaHttps;
use App\Support\DialogFlash;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Src\Identity\Domain\Peran;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: ['127.0.0.1', '::1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fc00::/7']);
        $middleware->append([AmankanHeader::class, SesiAmanBilaHttps::class]);
        $middleware->web(append: [HandleInertiaRequests::class]);
        $middleware->alias([
            'akun.aktif' => PastikanAkunAktif::class,
            'password.sudah-diganti' => PastikanPasswordSudahDiganti::class,
            'satu.sesi' => PastikanSatuSesi::class,
            'admin' => PastikanAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('masuk'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->peran === Peran::Admin ? '/admin' : '/peluang-instansi');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $respons, Throwable $galat, Request $request) {
            $kode = $respons->getStatusCode();

            if ($request->expectsJson() || ! in_array($kode, [403, 404, 419, 429, 500, 503], true) || ($kode >= 500 && app()->hasDebugModeEnabled())) {
                return $respons;
            }

            if ($kode === 419) {
                return back()->with('dialog', DialogFlash::peringatan('Sesi halaman habis', 'Halaman terlalu lama terbuka. Muat ulang halaman lalu coba lagi.'));
            }

            if (! $request->hasSession() || ! $request->session()->isStarted()) {
                Inertia::share(['aplikasi' => ['nama' => config('app.name')], 'whatsappUrl' => null, 'auth' => ['pengguna' => null], 'panduan' => false, 'dialog' => null]);
            }

            return Inertia::render('Galat', ['kode' => $kode])->toResponse($request)->setStatusCode($kode);
        });
    })->create();
