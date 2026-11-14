<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Src\Identity\Domain\Peran;
use Symfony\Component\HttpFoundation\Response;

final class PastikanAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->peran === Peran::Admin, 403);

        return $next($request);
    }
}
