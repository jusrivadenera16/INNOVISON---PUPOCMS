<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HealthProfileDocumentCorrectionTest extends TestCase
{
    public function test_it_keeps_an_approved_record_approved_when_document_correction_is_requested(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'User',
            'user_role' => User::ROLE_STUDENT,
        ]);

        $profile = HealthProfile::query()->create([
            'user_id' => $user->id,
            'clearance_status' => 'Issued',
            'verified_at' => now()->subDays(7),
            'documents_valid' => true,
            'pending_reason' => null,
            'resubmission_required_documents' => null,
            'resubmission_requested_at' => null,
            'puptas_synced_at' => now()->subDays(6),
            'puptas_sync_status' => 'synced',
        ]);

        $request = Request::create('/health-profile/' . $profile->id . '/request-resubmission', 'POST', [
            'pending_reason' => 'Please upload the updated medical certificate.',
            'resubmission_required_documents' => ['medical_certificate'],
            'needs_health_form_correction' => false,
        ]);

        $controller = app(AdminController::class);

        $response = $controller->requestHealthProfileResubmission($request, $profile->id);

        $profile->refresh();

        $this->assertEquals('Issued', $profile->clearance_status);
        $this->assertNotNull($profile->verified_at);
        $this->assertNotNull($profile->puptas_synced_at);
        $this->assertSame(['medical_certificate'], $profile->resubmission_required_documents);
        $this->assertTrue((bool) $profile->documents_valid);
        $this->assertNotNull($profile->resubmission_requested_at);
        $this->assertEquals(302, $response->getStatusCode());
    }
}
