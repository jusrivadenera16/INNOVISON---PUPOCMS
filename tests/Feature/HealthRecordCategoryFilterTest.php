<?php

namespace Tests\Feature;

use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile};
use App\Services\HealthRecordCategoryFilter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class HealthRecordCategoryFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) { $table->id(); });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->unsignedBigInteger('employee_health_profile_id')->nullable();
            $table->string('status');
            $table->string('category')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_filter_uses_latest_approved_category_not_profile_or_older_versions(): void
    {
        $student = (new HealthProfile)->forceFill(['id' => 11, 'health_form_category' => 'General']);
        HealthFormSubmission::create(['health_profile_id' => 11, 'category' => 'Regular', 'status' => 'approved', 'approved_at' => now()->subDay()]);
        HealthFormSubmission::create(['health_profile_id' => 11, 'category' => 'OJT', 'status' => 'approved', 'approved_at' => now()]);
        HealthFormSubmission::create(['health_profile_id' => 11, 'category' => 'Annual', 'status' => 'submitted']);
        $filter = app(HealthRecordCategoryFilter::class);
        $this->assertCount(1, $filter->filter(collect([$student]), 'On the Job Training', true));
        $this->assertCount(0, $filter->filter(collect([$student]), 'Regular', true));
        $this->assertCount(1, $filter->filter(collect([$student]), 'Annual'));
        $this->assertCount(0, $filter->filter(collect([$student]), 'OJT'));
    }

    public function test_student_and_employee_ids_do_not_mix_and_cancelled_categories_are_ignored(): void
    {
        $student = (new HealthProfile)->forceFill(['id' => 1, 'health_form_category' => 'Regular']);
        $employee = (new EmployeeHealthProfile)->forceFill(['id' => 1, 'health_form_category' => 'General']);
        HealthFormSubmission::create(['health_profile_id' => 1, 'category' => 'On the Job Training', 'status' => 'approved', 'approved_at' => now()]);
        HealthFormSubmission::create(['employee_health_profile_id' => 1, 'category' => 'Annual', 'status' => 'approved', 'approved_at' => now()]);
        HealthFormSubmission::create(['employee_health_profile_id' => 1, 'category' => 'OJT', 'status' => 'cancelled']);
        $filter = app(HealthRecordCategoryFilter::class);
        $records = collect([$student, $employee]);
        $this->assertSame($student, $filter->filter($records, 'OJT', true)->first());
        $this->assertSame($employee, $filter->filter($records, 'Annual', true)->first());
        $this->assertCount(2, $filter->filter($records, ''));
    }

    public function test_version_labels_show_category_date_and_latest_only_for_current_version(): void
    {
        $latest = view('admin.partials.health-form-version-label', ['versionCategory' => 'OJT', 'versionDateLabel' => 'Nov 11, 2026', 'versionLatest' => true])->render();
        $this->assertStringContainsString('On the Job Training', $latest);
        $this->assertStringContainsString('Nov 11, 2026', $latest);
        $this->assertStringContainsString('>Latest</span>', $latest);
        $this->assertStringNotContainsString('Current Health Profile', $latest);
        $previous = view('admin.partials.health-form-version-label', ['versionCategory' => 'Annual', 'versionDateLabel' => 'Oct 11, 2026', 'versionLatest' => false])->render();
        $this->assertStringContainsString('Annual', $previous);
        $this->assertStringNotContainsString('>Latest</span>', $previous);
    }

    public function test_active_ojt_submission_overrides_regular_account_approval_shortcut(): void
    {
        $user = new \App\Models\User(['user_type' => 'student', 'student_type' => 'regular']);
        $user->setRelation('healthProfile', null);
        $student = (new HealthProfile)->forceFill(['id' => 11, 'health_form_category' => 'Regular']);
        $student->setRelation('user', $user);
        $filter = app(HealthRecordCategoryFilter::class);
        $this->assertTrue($filter->isRegularStudentReview($student));
        HealthFormSubmission::create(['health_profile_id' => 11, 'category' => 'Regular', 'status' => 'approved']);
        foreach (['OJT', 'On the Job Training', 'On-the-job Training'] as $category) {
            $submission = HealthFormSubmission::create(['health_profile_id' => 11, 'category' => $category, 'status' => 'submitted']);
            $this->assertFalse($filter->isRegularStudentReview($student));
            $submission->update(['status' => 'cancelled']);
            $this->assertTrue($filter->isRegularStudentReview($student));
        }
        $student->health_form_category = 'OJT';
        HealthFormSubmission::create(['health_profile_id' => 11, 'category' => 'Regular', 'status' => 'submitted']);
        $this->assertTrue($filter->isRegularStudentReview($student));
    }

    public function test_student_information_modal_has_no_category_change_action(): void
    {
        $source = file_get_contents(resource_path('views/admin/health_records.blade.php'));
        $this->assertStringNotContainsString("@include('admin.partials.temporary-student-category-menu')", $source);
        $this->assertSame(3, substr_count($source, '->isRegularStudentReview('));
    }

    public function test_regular_shiftee_and_returnee_use_direct_approval_but_active_ojt_always_uses_review(): void
    {
        $filter = app(HealthRecordCategoryFilter::class);
        foreach (['regular', 'shiftee', 'returnee'] as $index => $studentType) {
            $user = new \App\Models\User(['user_type' => 'student', 'student_type' => $studentType]);
            $user->setRelation('healthProfile', null);
            $profile = (new HealthProfile)->forceFill(['id' => 100 + $index, 'health_form_category' => ucfirst($studentType)]);
            $profile->setRelation('user', $user);
            $this->assertTrue($filter->isRegularStudentReview($profile));
            $filter->preloadReviewCategories(collect([$profile]));
            $this->assertTrue($filter->isRegularStudentReview($profile));
            foreach (['OJT', 'On the Job Training', 'On-the-job Training'] as $category) {
                $submission = HealthFormSubmission::create(['health_profile_id' => $profile->id, 'category' => $category, 'status' => 'submitted']);
                $profile->setAttribute('review_category_preloaded', false);
                $this->assertFalse($filter->isRegularStudentReview($profile));
                $filter->preloadReviewCategories(collect([$profile]));
                $this->assertFalse($filter->isRegularStudentReview($profile));
                $submission->update(['status' => 'cancelled']);
            }
            $filter->preloadReviewCategories(collect([$profile]));
            $this->assertTrue($filter->isRegularStudentReview($profile));
        }
    }

    public function test_applicants_and_employees_do_not_gain_direct_student_approval(): void
    {
        foreach ([['applicant', 'new'], ['applicant', 'transferee'], ['faculty', null], ['employee', null], ['student', 'ojt'], ['student', 'ladderized']] as [$type, $studentType]) {
            $user = new \App\Models\User(['user_type' => $type, 'student_type' => $studentType]);
            $user->setRelation('healthProfile', null);
            $profile = (new HealthProfile)->forceFill(['id' => 999, 'health_form_category' => 'Student']);
            $profile->setRelation('user', $user);
            $this->assertFalse(app(HealthRecordCategoryFilter::class)->isRegularStudentReview($profile));
        }
    }
}
