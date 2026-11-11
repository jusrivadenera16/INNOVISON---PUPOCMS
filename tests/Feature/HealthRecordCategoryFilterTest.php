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
}
