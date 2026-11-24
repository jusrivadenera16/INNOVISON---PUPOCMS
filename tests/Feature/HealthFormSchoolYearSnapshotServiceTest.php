<?php

namespace Tests\Feature;

use App\Services\HealthFormSchoolYearSnapshotService;
use Tests\TestCase;

class HealthFormSchoolYearSnapshotServiceTest extends TestCase
{
    public function test_snapshot_keeps_school_year_and_is_bound_to_the_form_owner(): void
    {
        $service = app(HealthFormSchoolYearSnapshotService::class);
        $token = $service->issue(42, '2026-2027');

        $this->assertSame('2026-2027', $service->resolve($token, 42));
        $this->assertNull($service->resolve($token, 43));
    }

    public function test_invalid_or_tampered_snapshot_is_rejected(): void
    {
        $service = app(HealthFormSchoolYearSnapshotService::class);
        $token = $service->issue(42, '2026-2027');

        $this->assertNull($service->resolve($token . 'tampered', 42));
        $this->assertNull($service->resolve(null, 42));
        $this->assertNull($service->resolve($service->issue(42, '2026-2028'), 42));
    }
}
