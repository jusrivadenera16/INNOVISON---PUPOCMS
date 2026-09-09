<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\LoginController;
use ReflectionMethod;
use Tests\TestCase;

class LoginControllerIdpProfileTest extends TestCase
{
    public function test_account_type_is_preserved_when_it_is_outside_nested_identity_fields(): void
    {
        $controller = new LoginController();
        $extract = new ReflectionMethod($controller, 'extractProfilePayload');
        $accountType = new ReflectionMethod($controller, 'extractIdpAccountType');
        $payload = [
            'account_type' => 'Faculty',
            'data' => ['id' => 'faculty-id', 'email' => 'faculty@example.test'],
        ];

        $profile = $extract->invoke($controller, $payload);
        $this->assertSame('Faculty', $accountType->invoke($controller, $profile));
        $this->assertSame('faculty-id', data_get($profile, 'data.id'));
        $this->assertSame(['accountType' => 'Student'], $extract->invoke($controller, ['accountType' => 'Student']));
    }

    public function test_fresh_roles_replace_stale_token_aliases_and_array_entries(): void
    {
        $controller = new LoginController();
        $merge = new ReflectionMethod($controller, 'mergeIdpProfilePayloads');
        $extract = new ReflectionMethod($controller, 'extractRawRoles');

        foreach (['faculty', ['faculty'], []] as $freshRoles) {
            $profile = $merge->invoke($controller, [
                'role' => 'student',
                'roles' => ['student', 'superadmin'],
                'user' => ['role' => 'admin'],
                'reference_number' => '2026-1234-5678',
            ], ['roles' => $freshRoles]);

            $this->assertSame($freshRoles === [] ? [] : ['faculty'], $extract->invoke($controller, $profile));
            $this->assertArrayNotHasKey('role', $profile);
            $this->assertSame('2026-1234-5678', $profile['reference_number']);
        }
    }

    public function test_nested_me_roles_take_precedence_over_top_level_token_roles(): void
    {
        $controller = new LoginController();
        $merge = new ReflectionMethod($controller, 'mergeIdpProfilePayloads');
        $extract = new ReflectionMethod($controller, 'extractRawRoles');
        $profile = $merge->invoke($controller, ['roles' => ['student']], [
            'data' => ['user' => ['roles' => 'faculty']],
        ]);

        $this->assertSame(['faculty'], $extract->invoke($controller, $profile));
    }

    public function test_idp_privileged_roles_never_grant_local_admin_roles(): void
    {
        $controller = new LoginController();
        $map = new ReflectionMethod($controller, 'mapIdpRolesToLocal');
        foreach (['admin', 'superadmin', 'super_admin', 'cms:superadmin', 'ocms:admin', 'student_assistant', 'unknown'] as $role) {
            $this->assertSame('student', $map->invoke($controller, [$role], $role), $role);
        }
    }

    public function test_it_maps_the_applicant_idp_role_to_the_student_guard_and_applicant_user_type(): void
    {
        $controller = new LoginController();

        $mapRole = new ReflectionMethod($controller, 'mapSingleIdpRoleToLocal');
        $mapRole->setAccessible(true);
        $defaultUserType = new ReflectionMethod($controller, 'defaultUserTypeForIdpRole');
        $defaultUserType->setAccessible(true);

        $this->assertSame('student', $mapRole->invoke($controller, 'applicant'));
        $this->assertSame('Applicant', $defaultUserType->invoke($controller, 'applicant', 'student'));
    }

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

    public function test_it_extracts_the_idp_subject_from_a_jwt_access_token(): void
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'extractJwtClaims');
        $method->setAccessible(true);

        $encode = static fn (array $value): string => rtrim(strtr(
            base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES)),
            '+/',
            '-_'
        ), '=');

        $token = $encode(['alg' => 'none', 'typ' => 'JWT'])
            . '.'
            . $encode([
                'sub' => '5c26bd95-eaee-4931-9706-039931efecd5',
                'email' => 'lofi@example.test',
            ])
            . '.signature';

        $claims = $method->invoke($controller, $token);

        $this->assertIsArray($claims);
        $this->assertSame('5c26bd95-eaee-4931-9706-039931efecd5', $claims['sub']);
        $this->assertSame('lofi@example.test', $claims['email']);
    }

    public function test_it_ignores_an_opaque_access_token(): void
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'extractJwtClaims');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($controller, 'opaque-access-token'));
    }
}
