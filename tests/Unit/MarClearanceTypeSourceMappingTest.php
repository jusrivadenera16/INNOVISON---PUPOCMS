<?php

namespace Tests\Unit;

use App\Http\Controllers\MarClearanceTypeController;
use App\Models\MarClearanceSubcategorySource;
use App\Services\MarClearanceIssuanceService;
use ReflectionMethod;
use Tests\TestCase;

class MarClearanceTypeSourceMappingTest extends TestCase
{
    public function test_new_and_transferee_applicant_record_sources_use_applicant_final_review(): void
    {
        $controller = new MarClearanceTypeController();
        $method = new ReflectionMethod($controller, 'workflowSourcesForDataSource');
        $method->setAccessible(true);

        $expected = [MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW];
        $this->assertSame($expected, $method->invoke($controller, 'freshmen_applicants'));
        $this->assertSame($expected, $method->invoke($controller, 'transferee_applicants'));
    }

    public function test_health_form_category_sources_use_their_own_review_workflows(): void
    {
        $controller = new MarClearanceTypeController();
        $method = new ReflectionMethod($controller, 'workflowSourcesForDataSource');
        $method->setAccessible(true);

        $this->assertSame([MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW], $method->invoke($controller, 'student_health_form_category'));
        $this->assertSame([MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW], $method->invoke($controller, 'employee_health_form_category'));
    }

    public function test_transferee_source_does_not_persist_a_health_form_category(): void
    {
        $controller = new MarClearanceTypeController();
        $method = new ReflectionMethod($controller, 'sourceMappingAttributes');
        $method->setAccessible(true);

        $attributes = $method->invoke($controller, [
            'source_key' => 'transferee_applicants',
        ]);

        $this->assertSame('transferee_applicants', $attributes['source_key']);
        $this->assertNull($attributes['source_category_id']);
    }

    public function test_student_and_employee_source_mappings_persist_the_selected_category(): void
    {
        $controller = new MarClearanceTypeController();
        $method = new ReflectionMethod($controller, 'sourceMappingAttributes');
        $method->setAccessible(true);

        foreach (['student_health_form_category', 'employee_health_form_category'] as $sourceKey) {
            $attributes = $method->invoke($controller, [
                'source_key' => $sourceKey,
                'source_category_id' => 24,
            ]);

            $this->assertSame($sourceKey, $attributes['source_key']);
            $this->assertSame(24, $attributes['source_category_id']);
        }
    }

    public function test_data_sources_split_new_and_transferee_and_restrict_categories_to_student_or_employee_sources(): void
    {
        $keys = (new \ReflectionClass(MarClearanceTypeController::class))->getConstant('DATA_SOURCE_KEYS');

        $this->assertContains('freshmen_applicants', $keys);
        $this->assertContains('transferee_applicants', $keys);
        $this->assertContains('student_health_form_category', $keys);
        $this->assertContains('employee_health_form_category', $keys);
        $this->assertNotContains('applicant_health_form_category', $keys);
        $this->assertNotContains('health_form_category', $keys);
        $this->assertNotContains('student_health_records', $keys);
        $this->assertNotContains('faculty_health_records', $keys);
        $this->assertNotContains('admin_employee_health_records', $keys);
    }

    public function test_source_category_matching_is_exact_after_normalization(): void
    {
        $category = new \App\Models\HealthFormCategory(['name' => 'Annual Medical Clearance']);
        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'matchesSourceCategory');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, ' annual   medical clearance ', $category));
        $this->assertFalse($method->invoke($service, 'Transfer Students', $category));
    }

    public function test_transferee_selector_is_used_for_transferee_applicant_submissions(): void
    {
        $user = new \App\Models\User();
        $user->applicant_type = 'transferee';

        $submission = new \App\Models\HealthFormSubmission();
        $submission->setRelation('user', $user);
        $submission->setRelation('healthProfile', null);

        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'isTransfereeApplicantSubmission');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, $submission));
    }

    public function test_applicant_submissions_without_freshmen_reference_use_applicant_review_workflow(): void
    {
        $user = new \App\Models\User();
        $user->user_type = 'Applicant';

        $submission = new \App\Models\HealthFormSubmission();
        $submission->setRelation('user', $user);
        $submission->setRelation('healthProfile', null);

        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'reportWorkflowsForHealthFormSubmission');
        $method->setAccessible(true);

        $this->assertSame(
            [MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW],
            $method->invoke($service, $submission)
        );
    }

    public function test_submission_snapshot_preserves_applicant_workflow_after_account_becomes_student(): void
    {
        $user = new \App\Models\User();
        $user->user_type = 'Student';

        $submission = new \App\Models\HealthFormSubmission();
        $submission->setRelation('user', $user);
        $submission->setRelation('healthProfile', null);
        $submission->profile_snapshot = [
            'profile' => ['reference_number' => 'CLN-TEST-123'],
            'user' => ['user_type' => 'Applicant'],
        ];

        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'reportWorkflowsForHealthFormSubmission');
        $method->setAccessible(true);

        $this->assertSame(
            [MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW],
            $method->invoke($service, $submission)
        );
    }
}
