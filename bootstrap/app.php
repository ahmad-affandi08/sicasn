<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PastikanAdmin;
use App\Http\Middleware\PastikanAkunAktif;
use App\Http\Middleware\PastikanPasswordSudahDiganti;
use App\Http\Middleware\PastikanSatuSesi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [HandleInertiaRequests::class]);
        $middleware->alias([
            'akun.aktif' => PastikanAkunAktif::class,
            'password.sudah-diganti' => PastikanPasswordSudahDiganti::class,
            'satu.sesi' => PastikanSatuSesi::class,
            'admin' => PastikanAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('masuk'));
        $middleware->redirectUsersTo('/akun');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
