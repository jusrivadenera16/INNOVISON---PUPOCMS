<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersTest extends TestCase
{
    public function test_each_response_has_a_fresh_nonce_shared_with_the_rendered_document(): void
    {
        $nonces = [];
        foreach (['/', '/admin/dashboard', '/admin/appointments', '/admin/inventory', '/student/home', '/student/profile', '/student/booking', '/student/health-form/student', '/student/faq'] as $path) {
            $request = Request::create('http://localhost'.$path);
            $response = (new SecurityHeaders)->handle($request, function (Request $request) use (&$nonces): Response {
                $nonce = $request->attributes->get('csp_nonce');
                $nonces[] = $nonce;

                return new Response('<script nonce="'.$nonce.'">window.ready = true;</script>');
            });

            $nonce = $request->attributes->get('csp_nonce');
            $policy = $response->headers->get('Content-Security-Policy');
            self::assertFalse($response->headers->has('Content-Security-Policy-Report-Only'));
            self::assertSame(18, strlen(base64_decode($nonce, true)));
            self::assertStringContainsString("'nonce-{$nonce}'", $policy);
            self::assertStringContainsString('nonce="'.$nonce.'"', $response->getContent());
            self::assertStringContainsString("script-src-attr 'none'", $policy);
            self::assertStringContainsString("style-src-attr 'none'", $policy);
            self::assertStringNotContainsString('unsafe-inline', $policy);
            self::assertStringNotContainsString("'unsafe-eval'", $policy);
            self::assertStringNotContainsString('unsafe-hashes', $policy);
        }
        self::assertCount(9, array_unique($nonces));
    }

    public function test_address_lookup_and_accessibility_dependencies_have_explicit_sources(): void
    {
        $response = (new SecurityHeaders)->handle(Request::create('http://localhost/admin/walkin'), fn () => new Response);
        $policy = $response->headers->get('Content-Security-Policy');

        self::assertStringContainsString('https://raw.githubusercontent.com/maikkoko/postal-ph/master/data/', $policy);
        self::assertStringContainsString('https://psgc.gitlab.io', $policy);
        self::assertStringContainsString('https://website-widgets.pages.dev', $policy);
        self::assertStringContainsString("'wasm-unsafe-eval'", $policy);
        self::assertStringContainsString('https://cdn.jsdelivr.net/npm/tesseract.js-core@5.1.1/', $policy);
        self::assertStringContainsString("worker-src 'self' blob:", $policy);
        self::assertStringContainsString("form-action 'self'", $policy);
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    public function test_botpress_style_blocks_are_hash_pinned_without_allowing_inline_attributes(): void
    {
        $response = (new SecurityHeaders)->handle(Request::create('http://localhost/'), fn () => new Response);
        $policy = $response->headers->get('Content-Security-Policy');
        preg_match('/(?:^|; )style-src ([^;]+)/', $policy, $style);
        preg_match('/(?:^|; )style-src-attr ([^;]+)/', $policy, $attributes);

        foreach ([
            'oiLYcAg0EjF+m1EXCSd2pSwjU2PNoJ9uTolI0559Ncg=',
            'JvsjI3eIYQJtzBSap01PAk+kk7a+g4EOx96d3g3sN/k=',
            '3S10SDmyzdDbj+am5Vc2gJgpydr3HBh7Fu3HCLwXkSg=',
        ] as $hash) {
            self::assertStringContainsString("'sha256-{$hash}'", $style[1]);
        }
        self::assertSame("'none'", $attributes[1]);
        self::assertStringNotContainsString('unsafe-inline', $style[1]);
        self::assertStringNotContainsString('unsafe-hashes', $policy);
        self::assertStringContainsString("script-src-attr 'none'", $policy);
    }
}
