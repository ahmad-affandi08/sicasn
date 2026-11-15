<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SesiAmanBilaHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        config(['session.secure' => $request->isSecure()]);

        return $next($request);
    }
}
