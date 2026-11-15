<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AmankanHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(24);
        Vite::useCspNonce($nonce);

        $respons = $next($request);

        $respons->headers->set('Content-Security-Policy', $this->kebijakanKonten($nonce));
        $respons->headers->set('X-Frame-Options', 'DENY');
        $respons->headers->set('X-Content-Type-Options', 'nosniff');
        $respons->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respons->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $respons->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure()) {
            $respons->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respons;
    }

    private function kebijakanKonten(string $nonce): string
    {
        $pengembangan = file_exists(public_path('hot'));
        $sumberVite = $pengembangan ? ' '.trim((string) file_get_contents(public_path('hot'))).' ws:' : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'".$sumberVite,
            "style-src 'self' 'unsafe-inline'".$sumberVite,
            "img-src 'self' data:",
            "font-src 'self'".$sumberVite,
            "connect-src 'self'".$sumberVite,
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
    }
}
