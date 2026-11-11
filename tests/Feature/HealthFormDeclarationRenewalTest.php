<?php

namespace Tests\Feature;

use App\Http\Controllers\AppointmentController;
use App\Models\{HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use App\Services\{HealthFileStorage, HealthProfileSnapshotService, StudentHealthDeclarationService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthFormDeclarationRenewalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_saved_info_creates_a_category_specific_declaration_and_keeps_the_old_document(): void
    {
        Storage::fake('health_private');
        $files = app(HealthFileStorage::class);
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->andReturnSelf();
        $pdf->shouldReceive('output')->andReturn('%PDF-new-declaration');
        $declarations = [];
        Pdf::shouldReceive('loadView')->with('student.print_declaration_form', \Mockery::on(function ($data) use (&$declarations) {
            $declarations[] = $data;
            return true;
        }))->twice()->andReturn($pdf);
        Pdf::shouldReceive('loadView')->with('student.print_health_form', \Mockery::type('array'))->twice()->andReturn($pdf);
        $newPaths = [];
        foreach (['OJT' => 'On-the-Job Training (OJT)', 'Return to School' => 'returning to school'] as $category => $expectedPurpose) {
            $user = User::factory()->create(['user_role' => User::ROLE_STUDENT, 'user_type' => 'student', 'student_type' => 'ladderized']);
            $oldPath = 'health_profiles/health_declarations/previous-' . $user->id . '.pdf';
            $files->put($oldPath, '%PDF-old-approved-declaration');
            $profile = HealthProfile::create(['user_id' => $user->id, 'clearance_status' => 'Issued', 'health_form_category' => 'Ladderized', 'age' => 20, 'health_declaration' => $oldPath]);
            $approved = HealthFormSubmission::create([
                'user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'approved', 'category' => 'Ladderized',
                'approved_at' => now()->subYear(), 'profile_snapshot' => app(HealthProfileSnapshotService::class)->capture($profile),
            ]);
            $pending = HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'requested', 'category' => $category, 'requested_at' => now()]);
            HealthProfileCorrectionRequest::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'health_form_submission_id' => $pending->id, 'type' => 'new_health_form', 'status' => 'pending']);
            $this->actingAs($user, 'student');
            app(AppointmentController::class)->submitExistingHealthForm(Request::create('/student/health-form/use-existing', 'POST', ['submission_id' => $pending->id]));
            $pending->refresh();
            $newPath = data_get($pending->profile_snapshot, 'profile.health_declaration');
            $newPaths[] = $newPath;
            $this->assertNotSame($oldPath, $newPath);
            $this->assertTrue($files->exists($newPath));
            $this->assertSame('%PDF-old-approved-declaration', $files->get($oldPath));
            $this->assertSame($oldPath, $profile->fresh()->health_declaration);
            $this->assertSame($oldPath, data_get($approved->fresh()->profile_snapshot, 'profile.health_declaration'));
            $queueProfile = $profile->fresh();
            $queueMethod = new \ReflectionMethod(\App\Http\Controllers\AdminController::class, 'applySubmittedRenewalQueueState');
            $queueMethod->setAccessible(true);
            $queueMethod->invoke(app(\App\Http\Controllers\AdminController::class), collect([$queueProfile]));
            $this->assertSame($newPath, $queueProfile->health_declaration);
            $this->assertSame($pending->id, $queueProfile->pendingHealthFormSubmission->id);
            $this->assertSame($category, $queueProfile->health_form_category);
            $document = app(\App\Http\Controllers\WalkInController::class)->showApplicantDocument($profile->fresh(), 'health_declaration');
            $this->assertStringContainsString(basename($newPath), $document->headers->get('Content-Disposition'));
            $data = $declarations[array_key_last($declarations)];
            $this->assertSame($category, $data['categorySelected']);
            $this->assertSame($expectedPurpose, $data['purposeUnderline1']);
            $this->assertSame($pending->submitted_at->format('m/d/Y'), $data['signatureDate']);
            $html = view('student.print_declaration_form', $data)->render();
            $this->assertStringContainsString($expectedPurpose, $html);
            $this->assertStringNotContainsString('<u>Ladderized Program</u>', $html);
        }
        $this->assertCount(2, array_unique($newPaths));
    }

    public function test_custom_categories_and_student_type_fallback_are_resolved_consistently(): void
    {
        $service = app(StudentHealthDeclarationService::class);
        $this->assertSame('Annual / School Year Update', $service->purpose('Annual / School Year Update', 'ladderized')['purpose']);
        $this->assertSame('Ladderized Program', $service->purpose('Student', 'ladderized')['purpose']);
        $this->assertSame('currently enrolled student', $service->purpose('General', 'regular')['purpose']);
    }
}
