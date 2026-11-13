<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use App\Services\{EmployeeHealthFormHistoryService, HealthRecordCategoryFilter, HealthRecordSchemaCache};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Schema};
use Tests\TestCase;

class HealthRecordLoadingPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false, 'cache.default' => 'array']);
        Cache::flush();
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role', 'idp_role', 'student_number', 'student_id', 'employee_number', 'course', 'year', 'section', 'student_type', 'account_type', 'applicant_type', 'status'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        foreach (['admins', 'admin_hub', 'dependents_profiles'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id')->nullable();
                foreach (['name', 'email', 'employee_number', 'id_number', 'role', 'status'] as $column) $table->string($column)->nullable();
                $table->timestamps();
            });
        }
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id');
                foreach (['name', 'reference_number', 'student_number', 'student_id', 'employee_number', 'first_name', 'last_name', 'clearance_status', 'pullout_status', 'health_form_category', 'course_college', 'course_code', 'office', 'school_year', 'pending_reason', 'medical_condition_remarks', 'has_illness', 'medical_certificate', 'health_declaration', 'student_photo', 'final_review_draft_data'] as $column) $table->string($column)->nullable();
                foreach (['clearance_revoked_at', 'verified_at'] as $column) $table->timestamp($column)->nullable();
                $table->softDeletes(); $table->timestamps();
            });
        }
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id'] as $column) $table->unsignedBigInteger($column)->nullable();
            $table->string('status'); $table->string('category')->nullable(); $table->json('profile_snapshot')->nullable();
            foreach (['submitted_at', 'approved_at'] as $column) $table->timestamp($column)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id', 'health_form_submission_id'] as $column) $table->unsignedBigInteger($column)->nullable();
            $table->string('status'); $table->string('type'); $table->timestamp('submitted_at')->nullable(); $table->timestamps();
        });
        Schema::create('health_form_categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->boolean('is_active')->default(true); $table->json('available_for')->nullable();
        });
    }

    private function queriesFor(string $table): array
    {
        return array_values(array_filter(DB::getQueryLog(), fn ($query) => str_contains($query['query'], 'from "' . $table . '"') && str_starts_with($query['query'], 'select')));
    }

    public function test_only_the_displayed_approved_page_hydrates_full_profiles_and_search_still_finds_other_pages(): void
    {
        for ($i = 1; $i <= 45; $i++) {
            $user = User::forceCreate(['name' => sprintf('Student %03d', $i), 'user_type' => 'student', 'student_type' => 'regular']);
            $profile = HealthProfile::create(['user_id' => $user->id, 'clearance_status' => 'Fully Cleared',
                'course_college' => $i === 45 ? 'Target course' : 'Other course', 'health_form_category' => 'Regular',
                'verified_at' => now()->subMinutes($i), 'medical_certificate' => 'private/' . $i . '.pdf',
                'final_review_draft_data' => ['private_draft' => 'Not needed for list metadata']]);
            HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'approved', 'approved_at' => now(),
                'profile_snapshot' => ['profile' => ['medical_certificate' => 'old/' . $i . '.pdf']]]);
        }
        $request = Request::create('/health-records'); app()->instance('request', $request);
        DB::enableQueryLog(); DB::flushQueryLog();
        $data = app(AdminController::class)->viewHealth($request)->getData();
        $this->assertSame(45, $data['healthProfileSummaryRecords']->total());
        $this->assertCount(20, $data['healthProfileSummaryRecords']);
        $this->assertNull($data['records']->last()->final_review_draft_data);
        $this->assertSame('private/1.pdf', $data['healthProfileSummaryRecords']->first()->medical_certificate);
        $snapshots = array_filter($this->queriesFor('health_form_submissions'), fn ($entry) => str_starts_with($entry['query'], 'select * from "health_form_submissions"'));
        $this->assertCount(0, $snapshots, 'Issued rows must not load full approved snapshots.');
        $fullProfiles = array_filter($this->queriesFor('health_profiles'), fn ($entry) => str_starts_with($entry['query'], 'select * from "health_profiles"'));
        $this->assertCount(1, $fullProfiles);
        $this->assertStringContainsString('"id" in', array_values($fullProfiles)[0]['query']);
        foreach ([['q' => 'Student 045'], ['course' => 'Target course']] as $filters) {
            $request = Request::create('/health-records', 'GET', $filters); app()->instance('request', $request);
            $result = app(AdminController::class)->viewHealth($request)->getData()['healthProfileSummaryRecords'];
            $this->assertSame(1, $result->total());
            $this->assertSame('Student 045', $result->first()->user->name);
        }
        $request = Request::create('/health-records', 'GET', ['issued_page' => 3]); app()->instance('request', $request);
        $page = app(AdminController::class)->viewHealth($request)->getData()['healthProfileSummaryRecords'];
        $this->assertSame(45, $page->total()); $this->assertCount(5, $page);
        $this->assertSame('private/41.pdf', $page->first()->medical_certificate);
    }

    public function test_stats_reuse_page_counts_without_loading_profiles_and_invalidate_after_changes(): void
    {
        $user = User::forceCreate(['name' => 'Pending', 'user_type' => 'student']);
        $profile = HealthProfile::create(['user_id' => $user->id, 'clearance_status' => 'For Verification']);
        HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $request = Request::create('/health-records'); app()->instance('request', $request);
        app(AdminController::class)->viewHealth($request);
        DB::enableQueryLog(); DB::flushQueryLog();
        $stats = app(AdminController::class)->healthRecordStats($request)->getData(true)['stats'];
        $this->assertSame(1, $stats['pending_approval']);
        foreach (DB::getQueryLog() as $query) {
            $this->assertStringContainsString('COUNT(*)', $query['query']);
            $this->assertStringNotContainsString('profile_snapshot', $query['query']);
        }
        $profile->forceFill(['clearance_status' => 'Fully Cleared', 'verified_at' => now(), 'updated_at' => now()->addSecond()])->save();
        $stats = app(AdminController::class)->healthRecordStats($request)->getData(true)['stats'];
        $this->assertSame(0, $stats['pending_approval']); $this->assertSame(1, $stats['total_approved']);
        $cache = app(\App\Services\HealthRecordStatsCache::class);
        $other = Request::create('/health-records', 'GET', ['q' => 'Another name']);
        $this->assertNotSame($cache->key($request), $cache->key($other));
        $other = Request::create('/health-records'); $other->setUserResolver(fn () => (new User)->forceFill(['id' => 99, 'user_role' => 'admin']));
        $this->assertNotSame($cache->key($request), $cache->key($other));
    }

    public function test_pending_list_and_uncached_stats_exclude_the_blank_employee_shadow_without_deleting_either_profile(): void
    {
        $user = User::forceCreate(['name' => 'Faculty Queue', 'user_type' => 'faculty', 'employee_number' => '08114']);
        $shadow = HealthProfile::create(['user_id' => $user->id, 'clearance_status' => 'Pending']);
        $real = EmployeeHealthProfile::create(['user_id' => $user->id, 'employee_number' => '08114',
            'clearance_status' => 'For Verification', 'health_declaration' => 'private/actual-declaration.pdf']);
        HealthFormSubmission::create(['user_id' => $user->id, 'employee_health_profile_id' => $real->id,
            'status' => 'submitted', 'submitted_at' => now(), 'profile_snapshot' => ['profile' => ['health_declaration' => $real->health_declaration]]]);
        $conditionalUser = User::forceCreate(['name' => 'Compliance Student', 'user_type' => 'student']);
        HealthProfile::create(['user_id' => $conditionalUser->id, 'clearance_status' => 'Pending Resubmission', 'pending_reason' => 'Needs documents']);
        $request = Request::create('/health-records'); app()->instance('request', $request);
        $data = app(AdminController::class)->viewHealth($request)->getData();
        $this->assertFalse($data['records']->firstWhere('record_key', 'health:' . $shadow->id)->pending_approval_eligible);
        $this->assertTrue($data['records']->firstWhere('record_key', 'employee:' . $real->id)->pending_approval_eligible);

        // Execute the actual Blade queue classifier, without unrelated layout queries.
        $source = file_get_contents(resource_path('views/admin/health_records.blade.php'));
        $start = strpos($source, '        foreach ($records as $summaryRecord)');
        $end = strpos($source, "        \$healthSummaryStats['total_approved']", $start);
        $fragment = "@php\n" . substr($source, $start, $end - $start) . "\n@endphp\n{{ \$healthSummaryStats['pending_approval'] }}:{{ \$healthSummaryStats['pending_conditional'] }}";
        $counts = \Illuminate\Support\Facades\Blade::render($fragment, ['records' => $data['records'],
            'healthSummaryStats' => ['pending_approval' => 0, 'pending_conditional' => 0], 'pendingApprovalRecordIds' => [], 'pendingConditionalRecordIds' => []]);
        $this->assertSame('1:1', trim($counts));
        Cache::flush(); // Exercise the live calculation, not just the page's cached counts.
        $stats = app(AdminController::class)->healthRecordStats($request)->getData(true)['stats'];
        $this->assertSame(1, $stats['pending_approval']);
        $this->assertSame(1, $stats['pending_conditional']);
        $this->assertDatabaseHas('health_profiles', ['id' => $shadow->id, 'clearance_status' => 'Pending']);
        $this->assertDatabaseHas('health_profile_emp', ['id' => $real->id, 'health_declaration' => 'private/actual-declaration.pdf']);
        $this->assertDatabaseCount('health_form_submissions', 1);
    }

    public function test_encrypted_snapshot_reads_are_reused_but_raw_changes_invalidate_the_model_cache(): void
    {
        Schema::table('health_form_submissions', fn (Blueprint $table) => $table->text('profile_snapshot_encrypted')->nullable());
        config(['health_data_encryption.enabled' => true, 'health_data_encryption.key' => 'base64:' . base64_encode(str_repeat('h', 32))]);
        $request = Request::create('/health-records');
        $request->attributes->set(HealthRecordSchemaCache::REQUEST_ATTRIBUTE, new HealthRecordSchemaCache()); app()->instance('request', $request);
        $encrypter = \Mockery::mock(\App\Services\HealthDataEncrypter::class)->makePartial();
        $encrypter->shouldReceive('decrypt')->times(3)->passthru(); app()->instance(\App\Services\HealthDataEncrypter::class, $encrypter);
        $submission = HealthFormSubmission::create(['status' => 'approved', 'profile_snapshot' => ['profile' => ['name' => 'Original']]])->fresh();
        for ($i = 0; $i < 20; $i++) $this->assertSame('Original', $submission->snapshotProfile()['name']);
        $attributes = $submission->getAttributes();
        $attributes['profile_snapshot_encrypted'] = $encrypter->encrypt(json_encode(['profile' => ['name' => 'Changed']]));
        $submission->setRawAttributes($attributes);
        for ($i = 0; $i < 20; $i++) $this->assertSame('Changed', $submission->snapshotProfile()['name']);
        $next = Request::create('/health-records'); $next->attributes->set(HealthRecordSchemaCache::REQUEST_ATTRIBUTE, new HealthRecordSchemaCache()); app()->instance('request', $next);
        $this->assertSame('Changed', $submission->snapshotProfile()['name']);
        $this->assertStringNotContainsString('healthRecordReadCache', json_encode($submission));
    }

    public function test_fifty_student_review_categories_use_one_metadata_query_instead_of_fifty(): void
    {
        $profiles = collect();
        for ($id = 1; $id <= 50; $id++) {
            $profile = (new HealthProfile)->forceFill(['id' => $id, 'health_form_category' => 'Regular']);
            $user = new User(['user_type' => 'student', 'student_type' => 'regular']); $user->setRelation('healthProfile', null);
            $profile->setRelation('user', $user); $profiles->push($profile);
            HealthFormSubmission::create(['health_profile_id' => $id, 'category' => 'Regular', 'status' => 'approved', 'approved_at' => now()]);
            HealthFormSubmission::create(['health_profile_id' => $id, 'category' => 'OJT', 'status' => 'submitted']);
            HealthFormSubmission::create(['health_profile_id' => $id, 'category' => 'Ignore cancelled', 'status' => 'cancelled']);
        }
        $filter = app(HealthRecordCategoryFilter::class);
        DB::enableQueryLog(); DB::flushQueryLog();
        foreach ($profiles as $profile) $this->assertFalse($filter->isRegularStudentReview($profile));
        $this->assertCount(50, $this->queriesFor('health_form_submissions'));
        DB::flushQueryLog();
        $filter->preloadReviewCategories($profiles);
        foreach ($profiles as $profile) $this->assertFalse($filter->isRegularStudentReview($profile));
        $queries = $this->queriesFor('health_form_submissions');
        $this->assertCount(1, $queries);
        $this->assertStringNotContainsString('profile_snapshot', $queries[0]['query']);
    }

    public function test_employee_history_uses_two_batch_queries_and_preserves_latest_version_order(): void
    {
        $profiles = collect(); $expected = [];
        for ($id = 1; $id <= 40; $id++) {
            $profiles->push((new EmployeeHealthProfile)->forceFill(['id' => $id]));
            HealthFormSubmission::create(['employee_health_profile_id' => $id, 'status' => 'approved', 'approved_at' => '2026-11-02', 'submitted_at' => '2026-11-01', 'profile_snapshot' => ['profile' => ['name' => 'Newer approval']]]);
            HealthFormSubmission::create(['employee_health_profile_id' => $id, 'status' => 'approved', 'approved_at' => '2026-11-01', 'submitted_at' => '2026-11-01']);
        }
        $history = app(EmployeeHealthFormHistoryService::class);
        DB::enableQueryLog(); DB::flushQueryLog();
        foreach ($profiles as $profile) $expected[$profile->id] = $history->latestApproved($profile)->id;
        $this->assertCount(40, $this->queriesFor('health_form_submissions'));
        DB::flushQueryLog(); $history->preloadLatestApproved($profiles);
        foreach ($profiles as $profile) $this->assertSame($expected[$profile->id], $history->latestApproved($profile)->id);
        $this->assertCount(2, $this->queriesFor('health_form_submissions'));
    }

    public function test_schema_cache_is_request_local_and_does_not_repeat_column_queries_per_model(): void
    {
        DB::enableQueryLog(); DB::flushQueryLog();
        for ($i = 0; $i < 30; $i++) (new HealthProfile)->getCasts();
        $baseline = count(DB::getQueryLog());
        $request = Request::create('/health-records');
        $request->attributes->set(HealthRecordSchemaCache::REQUEST_ATTRIBUTE, new HealthRecordSchemaCache());
        app()->instance('request', $request);
        DB::flushQueryLog();
        for ($i = 0; $i < 30; $i++) (new HealthProfile)->getCasts();
        $optimized = count(DB::getQueryLog());
        $this->assertLessThan($baseline / 10, $optimized);
        $this->assertGreaterThan(0, $optimized);
        app()->instance('request', Request::create('/another-request'));
        DB::flushQueryLog(); (new HealthProfile)->getCasts();
        $this->assertGreaterThan($optimized, count(DB::getQueryLog()));
    }

    public function test_request_schema_cache_preserves_encrypted_medical_field_reads(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->json('medical_history')->nullable(); $table->text('medical_history_encrypted')->nullable();
        });
        config(['health_data_encryption.enabled' => true, 'health_data_encryption.key' => 'base64:' . base64_encode(str_repeat('h', 32))]);
        $request = Request::create('/health-records');
        $request->attributes->set(HealthRecordSchemaCache::REQUEST_ATTRIBUTE, new HealthRecordSchemaCache());
        app()->instance('request', $request);
        $user = User::forceCreate(['name' => 'Encrypted']);
        $profile = HealthProfile::create(['user_id' => $user->id, 'medical_history' => ['Asthma']]);
        $stored = DB::table('health_profiles')->where('id', $profile->id)->first();
        $this->assertStringNotContainsString('Asthma', (string) $stored->medical_history_encrypted);
        $this->assertSame(['Asthma'], $profile->fresh()->medical_history);
    }

    public function test_page_and_stats_keep_both_sources_and_do_not_mix_historical_user_data_into_pending_records(): void
    {
        $studentUser = User::forceCreate(['name' => 'Current Student', 'user_type' => 'student', 'student_number' => '2026-00001-TG-1', 'student_type' => 'regular']);
        $employeeUser = User::forceCreate(['name' => 'Current Employee', 'user_type' => 'faculty']);
        $student = HealthProfile::create(['user_id' => $studentUser->id, 'student_number' => $studentUser->student_number, 'clearance_status' => 'For Verification', 'health_form_category' => 'OJT']);
        $employee = EmployeeHealthProfile::create(['user_id' => $employeeUser->id, 'clearance_status' => 'For Verification']);
        $this->assertSame($student->id, $employee->id);
        foreach ([['health_profile_id', $student, $studentUser], ['employee_health_profile_id', $employee, $employeeUser]] as [$key, $profile, $user]) {
            HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id, 'status' => 'approved', 'approved_at' => now()->subDay(), 'submitted_at' => now()->subDays(2),
                'profile_snapshot' => ['profile' => ['clearance_status' => 'Fully Cleared', 'verified_at' => now()->subDay()->toIso8601String()], 'user' => ['name' => 'Historical ' . $user->name, 'first_name' => 'Historical', 'last_name' => $user->name]]]);
            $pending = HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id, 'status' => 'submitted', 'category' => 'OJT', 'submitted_at' => now()]);
            if ($key === 'health_profile_id') HealthProfileCorrectionRequest::create(['user_id' => $user->id, $key => $profile->id, 'health_form_submission_id' => $pending->id, 'type' => 'new_health_form', 'status' => 'submitted', 'submitted_at' => now()]);
        }
        $request = Request::create('/health-records'); app()->instance('request', $request);
        $data = app(AdminController::class)->viewHealth($request)->getData();
        $this->assertCount(2, $data['records']);
        $this->assertSame(2, $data['healthProfileSummaryRecords']->total());
        $this->assertSame('Current Student', $data['records']->firstWhere('record_key', 'health:' . $student->id)->user->name);
        $this->assertSame('Historical Current Employee', $data['healthProfileSummaryRecords']->getCollection()->firstWhere('record_source', 'employee')->user->name);
        $this->assertSame('Current Student', $studentUser->fresh()->name);
        $statsRequest = Request::create('/health-records/stats'); app()->instance('request', $statsRequest);
        $stats = app(AdminController::class)->healthRecordStats($statsRequest)->getData(true)['stats'];
        $this->assertSame(2, $stats['total_approved']);
        $this->assertSame(2, $stats['pending_approval']);
    }
}
