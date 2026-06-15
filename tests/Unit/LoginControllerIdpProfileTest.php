<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\LoginController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class LoginControllerIdpProfileTest extends TestCase
{
    public function test_it_preserves_a_reference_number_outside_the_nested_user_payload(): void
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'extractProfilePayload');
        $method->setAccessible(true);

        $profile = $method->invoke($controller, [
            'user' => [
                'id' => 'idp-user-123',
                'firstname' => 'Lofi',
                'middlename' => 'Hebru',
                'lastname' => 'Nuko',
                'email' => 'lofi@example.test',
            ],
            'application' => [
                'reference_number' => '2026-8889-8828',
            ],
        ]);

        $this->assertIsArray($profile);
        $this->assertSame('Lofi', data_get($profile, 'user.firstname'));
        $this->assertSame('2026-8889-8828', data_get($profile, 'application.reference_number'));
    }
}
