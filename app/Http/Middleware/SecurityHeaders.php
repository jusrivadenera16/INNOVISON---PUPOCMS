<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.botpress.cloud https://cdn.jsdelivr.net https://code.jquery.com https://files.bpcontent.cloud https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' data: https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https:",
            "frame-src 'self' https:",
        ]));

        // HSTS is only safe to advertise after the browser is already on HTTPS.
        if ($request->isSecure() && !app()->environment(['local', 'testing'])) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
