<?php

require __DIR__.'/../../vendor/autoload.php';

$request = Illuminate\Http\Request::create('http://localhost/admin/appointments');
$response = (new App\Http\Middleware\SecurityHeaders)->handle(
    $request,
    fn () => new Symfony\Component\HttpFoundation\Response
);

echo json_encode([
    'nonce' => $request->attributes->get('csp_nonce'),
    'policy' => $response->headers->get('Content-Security-Policy'),
], JSON_THROW_ON_ERROR);
