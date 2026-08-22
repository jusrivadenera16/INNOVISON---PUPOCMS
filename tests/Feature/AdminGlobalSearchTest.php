<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminGlobalSearchController;
use App\Models\User;
use App\Services\ModulePermissionService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AdminGlobalSearchTest extends TestCase
{
    public function test_it_returns_matching_accessible_page_shortcuts(): void
    {
        $response = $this->searchAs(new User(['user_role' => User::ROLE_ADMIN]), 'dashboard');

        $results = collect($response->getData(true)['groups'])
            ->flatMap(fn (array $group) => $group['results']);

        $this->assertTrue($results->contains(fn (array $result) => $result['title'] === 'Dashboard'));
    }

    public function test_it_does_not_return_pages_without_the_required_module_permission(): void
    {
        $response = $this->searchAs(new User(['user_role' => User::ROLE_ADMIN]), 'appointments');

        $results = collect($response->getData(true)['groups'])
            ->flatMap(fn (array $group) => $group['results']);

        $this->assertFalse($results->contains(fn (array $result) => $result['title'] === 'Appointments'));
    }

    private function searchAs(User $user, string $query)
    {
        $request = Request::create('/admin/global-search', 'GET', ['q' => $query]);
        $request->setUserResolver(fn () => $user);
        $permissions = Mockery::mock(ModulePermissionService::class);
        $permissions->shouldReceive('can')->andReturn(false);

        return app(AdminGlobalSearchController::class)->search(
            $request,
            $permissions
        );
    }
}
