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

    public function test_it_recognizes_an_oidc_subject_as_an_identity_field(): void
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'extractProfilePayload');
        $method->setAccessible(true);

        $profile = $method->invoke($controller, [
            'sub' => '5c26bd95-eaee-4931-9706-039931efecd5',
            'firstname' => 'Lofi',
            'lastname' => 'Nuko',
        ]);

        $this->assertIsArray($profile);
        $this->assertSame('5c26bd95-eaee-4931-9706-039931efecd5', $profile['sub']);
    }

    public function test_it_keeps_the_token_reference_when_user_info_only_contains_profile_fields(): void
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'mergeIdpProfilePayloads');
        $method->setAccessible(true);

        $profile = $method->invoke(
            $controller,
            [
                'user' => [
                    'id' => '1702',
                    'reference_number' => '2026-8889-8828',
                ],
                'application' => [
                    'reference_number' => '2026-8889-8828',
                ],
            ],
            [
                'user' => [
                    'id' => '1702',
                    'firstname' => 'Lofi',
                    'middlename' => 'Hebru',
                    'lastname' => 'Nuko',
                    'email' => 'lofi@example.test',
                ],
            ]
        );

        $this->assertSame('2026-8889-8828', data_get($profile, 'user.reference_number'));
        $this->assertSame('2026-8889-8828', data_get($profile, 'application.reference_number'));
        $this->assertSame('Lofi', data_get($profile, 'user.firstname'));
        $this->assertSame('lofi@example.test', data_get($profile, 'user.email'));
    }
}
