<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(18));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'wasm-unsafe-eval' https://cdnjs.cloudflare.com https://cdn.botpress.cloud https://cdn.jsdelivr.net https://code.jquery.com https://files.bpcontent.cloud https://unpkg.com",
            "script-src-attr 'none'",
            // Botpress v3.7 inserts these three reviewed styles into its shadow roots without nonces.
            "style-src 'self' 'nonce-{$nonce}' 'sha256-vNVqbO2I6xQA1MkNuBtHYeaq2yK2RBaK+PaWflH6DiY=' 'sha256-fekiPGlxr3o+AqFuyPctA0ie/UXl/bBRD0nv9gu1AYI=' 'sha256-vTnrYvOn4J5yXGO8qSjlIpmdMlOS+gZpPgQ9MWgZT28=' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "style-src-attr 'none'",
            "font-src 'self' data: https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.gstatic.com https://website-widgets.pages.dev",
            "img-src 'self' data: blob: https://img.icons8.com https://*.botpress.cloud https://*.bpcontent.cloud",
            "media-src 'self' data: blob: https://*.botpress.cloud https://*.bpcontent.cloud",
            "connect-src 'self' https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/ https://cdn.jsdelivr.net/npm/tesseract.js-core@5.1.1/ https://cdn.jsdelivr.net/npm/@tesseract.js-data/eng/4.0.0_best_int/ https://psgc.gitlab.io https://raw.githubusercontent.com/maikkoko/postal-ph/master/data/ https://identity-provider.isaxbsit2027.com https://*.botpress.cloud https://*.bpcontent.cloud wss://*.botpress.cloud wss://*.bpcontent.cloud",
            "frame-src 'self' blob: https://lottie.host https://*.botpress.cloud https://*.bpcontent.cloud",
            "worker-src 'self' blob:",
        ]));

        // HSTS is only safe to advertise after the browser is already on HTTPS.
        if ($request->isSecure() && !app()->environment(['local', 'testing'])) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
