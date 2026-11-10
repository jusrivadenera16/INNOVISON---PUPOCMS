<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentProfileUpdateValidationTest extends TestCase
{
    public function test_height_must_be_within_the_feet_range(): void
    {
        $this->assertProfileUpdateError(['height' => '9'], [], 'height');
    }

    public function test_weight_must_be_within_the_pounds_range(): void
    {
        $this->assertProfileUpdateError(['weight' => '0'], [], 'weight');
    }

    public function test_address_parts_must_be_completed_together(): void
    {
        $this->assertProfileUpdateError([
            'address' => '1 Main Street, Barangay One, City One',
            'address_street' => '1 Main Street',
            'address_barangay' => 'Barangay One',
            'address_city_municipality' => 'City One',
            'address_province' => '',
        ], [], 'address_province');
    }

    private function assertProfileUpdateError(array $input, array $files, string $field): void
    {
        Auth::shouldReceive('user')->once()->andReturn(new User());
        $request = Request::create('/student/update-contact', 'POST', $input, [], $files);

        try {
            (new AppointmentController())->updateContact($request);
            $this->fail("The {$field} validation should reject this profile update.");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
