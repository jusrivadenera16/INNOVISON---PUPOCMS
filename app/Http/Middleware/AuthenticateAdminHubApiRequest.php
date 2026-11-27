<?php

namespace App\Http\Middleware;

class AuthenticateAdminHubApiRequest extends AuthenticateExternalApiRequest
{
    // Admin Hub keeps token validation, permissions and logging, but never falls back to static keys.
    protected function allowsLegacyStaticKeys(): bool
    {
        return false;
    }
}
