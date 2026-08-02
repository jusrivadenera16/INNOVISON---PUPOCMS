<?php

namespace Tests\Feature;

use App\Http\Controllers\MaintenanceController;
use App\Models\SystemSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->timestamps();
        });

    }

    public function test_disabled_maintenance_redirects_to_the_normal_site_even_with_a_future_estimate(): void
    {
        SystemSetting::putValue('maintenance_mode_enabled', '0');
        SystemSetting::putValue('maintenance_estimated_completion', now()->addDay()->format('Y-m-d H:i'));

        $response = app(MaintenanceController::class)->show();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('landing'), $response->getTargetUrl());
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_enabled_maintenance_displays_the_page_without_browser_caching(): void
    {
        SystemSetting::putValue('maintenance_mode_enabled', '1');
        SystemSetting::putValue('maintenance_estimated_completion', now()->addHour()->format('Y-m-d H:i'));

        $response = app(MaintenanceController::class)->show();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Under Maintenance', (string) $response->getContent());
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
