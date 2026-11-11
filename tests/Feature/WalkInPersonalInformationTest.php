<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\{HealthProfile, User};
use Tests\TestCase;

class WalkInPersonalInformationTest extends TestCase
{
    public function test_information_payload_includes_account_year_level_and_section_for_students_and_applicants(): void
    {
        foreach (['Student', 'Applicant'] as $type) {
            $user = new User; $user->setRawAttributes(['name' => 'Sample Person', 'user_type' => $type, 'year' => '3rd Year', 'section' => '1']);
            $profile = new HealthProfile; $profile->setRawAttributes(['id' => 1]);
            $payload = (new \ReflectionMethod(WalkInController::class, 'healthProfileInformationPayload'))->invoke(app(WalkInController::class), $profile, $user);
            $this->assertSame('3rd Year', $payload['personal_information']['year_level']);
            $this->assertSame('1', $payload['personal_information']['section']);
        }
    }

    public function test_shared_modal_has_one_stable_height_and_a_scrollable_content_panel(): void
    {
        $view = file_get_contents(resource_path('views/admin/walkin.blade.php'));
        $this->assertStringContainsString('<h3>Personal Information</h3>', $view);
        $this->assertStringContainsString('data-information-button-label>Personal Information</span>', $view);
        $this->assertStringContainsString("['year_level', 'Year Level', 'readonly']", $view);
        $this->assertStringContainsString("['section', 'Section', 'readonly']", $view);
        $this->assertMatchesRegularExpression('/\.applicant-health-info-modal\s*\{[^}]*height:\s*min\(720px/s', $view);
        $this->assertMatchesRegularExpression('/\.health-info-content\s*\{[^}]*overflow-y:\s*auto/s', $view);
        $this->assertStringContainsString('<strong>Applicants Final Review</strong>', $view);
        $this->assertStringContainsString('data-final-review-title>Final Review</h4>', $view);
        $clearanceView = file_get_contents(resource_path('views/admin/partials/approved-clearance-review.blade.php'));
        $this->assertStringNotContainsString('approved-clearance-form', $clearanceView);
        $purposeView = file_get_contents(resource_path('views/admin/partials/review-medical-clearance.blade.php'));
        $this->assertStringNotContainsString('type="checkbox"', $purposeView);
        $this->assertStringNotContainsString('data-review-clearance-patient', $purposeView);
        $this->assertStringNotContainsString('data-review-clearance-date', $purposeView);
        $this->assertStringContainsString('data-review-clearance-purpose required', $purposeView);
    }
}
