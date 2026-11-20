<?php

namespace Tests\Unit;

use App\Rules\PhilippinePhoneNumber;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PhilippinePhoneNumberTest extends TestCase
{
    public function test_accepts_mobile_and_common_philippine_landline_formats(): void
    {
        foreach (['09123456789', '0912-345-6789', '02-8123-4567', '(02) 8123 4567', '032 123-4567'] as $phone) {
            $validator = Validator::make(['phone' => $phone], ['phone' => ['required', new PhilippinePhoneNumber()]]);
            $this->assertTrue($validator->passes(), "Expected {$phone} to be accepted.");
        }
    }

    public function test_rejects_invalid_or_ambiguous_phone_values(): void
    {
        foreach (['0912345678', '091234567890', '12345', '+63 2 8123-4567', '02-ABC-4567'] as $phone) {
            $validator = Validator::make(['phone' => $phone], ['phone' => ['required', new PhilippinePhoneNumber()]]);
            $this->assertFalse($validator->passes(), "Expected {$phone} to be rejected.");
        }
    }
}
