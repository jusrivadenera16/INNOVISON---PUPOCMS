<?php

namespace Tests\Feature;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\EmployeeHealthFormPdfService;
use App\Services\HealthFormPdfSnapshotService;
use App\Services\HealthProfileSnapshotService;
use App\Services\StoredImageDataUri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class HealthFormPdfRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('sis_reference_number')->nullable();
            $table->date('DOB')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('gender')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('course')->nullable();
            $table->string('year')->nullable();
            $table->string('section')->nullable();
            $table->boolean('is_health_profile_completed')->default(false);
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('school_year')->nullable();
            $table->string('health_form_category')->nullable();
            $table->string('home_address')->nullable();
            $table->string('digital_signature')->nullable();
            $table->string('medical_condition_remarks')->nullable();
            $table->boolean('documents_valid')->default(false);
            $table->string('medical_certificate')->nullable();
            $table->string('clearance_status')->nullable();
            $table->timestamp('clearance_revoked_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('pullout_status')->nullable();
            $table->date('birthday')->nullable();
            $table->timestamps();
        });

        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('category')->nullable();
            $table->string('school_year')->nullable();
            $table->string('status');
            $table->string('pdf_path')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->timestamp('snapshot_captured_at')->nullable();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->unsignedBigInteger('status_changed_by_user_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('uploaded_signature_path')->nullable();
            $table->longText('staff_signature')->nullable();
            $table->string('staff_health_form_pdf_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Storage::fake('health_private');
        Storage::fake('public');
        config([
            'health_files.write_disk' => 'health_private',
            'health_files.legacy_disk' => 'public',
            'health_files.legacy_fallback' => true,
            'health_files.mirror_to_legacy' => false,
            'health_files.delete_legacy_on_replace' => false,
        ]);
    }

    public function test_student_snapshot_refresh_preserves_review_state(): void
    {
        Carbon::setTestNow('2026-07-28 15:30:00');

        $user = User::forceCreate([
            'name' => 'Test Student',
            'student_number' => '2026-0001',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0001',
            'digital_signature' => 'health_profiles/signatures/test.png',
        ]);
        $oldPath = 'health_form_submissions/' . $user->id . '/old.pdf';
        $submittedAt = Carbon::parse('2026-07-20 09:00:00');
        $submission = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Enrollment',
            'status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            'pdf_path' => $oldPath,
            'submitted_at' => $submittedAt,
            'remarks' => 'Keep this review note.',
        ]);
        Storage::disk('health_private')->put($oldPath, 'old-pdf');

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('new-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) use ($profile, $submittedAt) {
                return (int) $data['profile']->id === (int) $profile->id
                    && $data['pdfMode'] === true
                    && $submittedAt->equalTo($data['healthFormSubmittedAt']);
            }))
            ->andReturn($dompdf);

        $refreshed = app(HealthFormPdfSnapshotService::class)->refreshExistingSnapshot($profile);

        $this->assertNotNull($refreshed);
        $this->assertSame($submission->id, $refreshed->id);
        $this->assertSame(HealthFormSubmission::STATUS_NEEDS_CORRECTION, $refreshed->status);
        $this->assertSame('Keep this review note.', $refreshed->remarks);
        $this->assertTrue($submittedAt->equalTo($refreshed->submitted_at));
        $this->assertNotSame($oldPath, $refreshed->pdf_path);
        Storage::disk('health_private')->assertMissing($oldPath);
        $this->assertSame('new-pdf', Storage::disk('health_private')->get($refreshed->pdf_path));
    }

    public function test_student_submission_saves_a_frozen_health_profile_snapshot(): void
    {
        Carbon::setTestNow('2026-08-23 10:15:00');

        $user = User::forceCreate([
            'name' => 'History Student',
            'email' => 'history@example.com',
            'student_number' => '2026-0100',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0100',
            'medical_condition_remarks' => 'Asthma',
            'documents_valid' => true,
            'medical_certificate' => 'health_profiles/medical_certificates/history.pdf',
        ]);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('snapshot-pdf');
        Pdf::shouldReceive('loadView')->once()->andReturn($dompdf);

        $submission = app(HealthFormPdfSnapshotService::class)->recordSubmittedWithoutPdf(
            $profile,
            $user,
            'Enrollment'
        );

        $this->assertSame('Asthma', data_get($submission->profile_snapshot, 'profile.medical_condition_remarks'));
        $this->assertTrue((bool) data_get($submission->profile_snapshot, 'profile.documents_valid'));
        $this->assertSame(
            'health_profiles/medical_certificates/history.pdf',
            data_get($submission->profile_snapshot, 'profile.medical_certificate')
        );
        $this->assertSame('History Student', data_get($submission->profile_snapshot, 'user.name'));
        $this->assertNotNull($submission->snapshot_captured_at);
    }

    public function test_new_form_request_preserves_the_previous_current_profile_before_overwrite(): void
    {
        Carbon::setTestNow('2026-08-23 11:00:00');

        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'student_number' => '2026-0200',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0200',
            'medical_condition_remarks' => 'Original assessment',
            'documents_valid' => true,
        ]);
        $current = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Initial Submission',
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $request = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Internship',
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        app(\App\Services\HealthProfileSnapshotService::class)
            ->preserveCurrentBeforeNewSubmission($profile, $request);

        $this->assertSame(
            'Original assessment',
            data_get($current->fresh()->profile_snapshot, 'profile.medical_condition_remarks')
        );
        $this->assertNull($request->fresh()->profile_snapshot);
    }

    public function test_approved_snapshot_fallback_does_not_take_over_a_newer_submitted_form(): void
    {
        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'email' => 'versioned@example.com',
            'student_number' => '2026-0300',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0300',
            'clearance_status' => 'Fully Cleared',
        ]);
        $approvedPath = 'health_form_submissions/' . $user->id . '/approved.pdf';
        $pendingPath = 'health_form_submissions/' . $user->id . '/pending.pdf';

        $approved = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $approvedPath,
            'profile_snapshot' => [
                'profile' => ['home_address' => 'Original approved address'],
                'user' => ['name' => 'Versioned Student'],
            ],
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $pending = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'pdf_path' => $pendingPath,
            'submitted_at' => now(),
        ]);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('rebuilt-approved-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) {
                return $data['profile']->home_address === 'Original approved address';
            }))
            ->andReturn($dompdf);

        $result = app(HealthFormPdfSnapshotService::class)->ensureApprovedSnapshot($profile);

        $this->assertSame($approved->id, $result->id);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $approved->fresh()->status);
        $this->assertSame('Original approved address', data_get($approved->fresh()->profile_snapshot, 'profile.home_address'));
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $pending->fresh()->status);
        $this->assertSame($pendingPath, $pending->fresh()->pdf_path);
        $this->assertSame('rebuilt-approved-pdf', Storage::disk('health_private')->get($result->pdf_path));
    }

    public function test_new_health_form_stays_staged_until_approval_then_promotes_over_the_old_version(): void
    {
        Carbon::setTestNow('2026-08-23 11:30:00');

        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'student_number' => '2026-0201',
            'DOB' => '2005-01-10',
            'contact_no' => '09111111111',
            'is_health_profile_completed' => true,
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0201',
            'school_year' => '2025-2026',
            'health_form_category' => 'Initial Submission',
            'home_address' => 'Old address',
            'medical_condition_remarks' => 'Original assessment',
            'clearance_status' => 'Fully Cleared',
            'documents_valid' => true,
        ]);
        $previousVersion = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Initial Submission',
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $request = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Academic Year 2026-2027',
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        app(HealthProfileSnapshotService::class)
            ->preserveCurrentBeforeNewSubmission($profile->fresh('user'), $request);

        $stagedUser = clone $user;
        $stagedUser->DOB = '2005-02-12';
        $stagedUser->contact_no = '09222222222';
        $stagedProfile = clone $profile;
        $stagedProfile->forceFill([
            'school_year' => '2026-2027',
            'health_form_category' => 'Academic Year 2026-2027',
            'home_address' => 'New address',
            'medical_condition_remarks' => 'Updated assessment',
            'clearance_status' => 'For Verification',
        ]);
        $stagedProfile->setRelation('user', $stagedUser);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('staged-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) {
                return $data['profile']->home_address === 'New address'
                    && $data['profile']->user->contact_no === '09222222222';
            }))
            ->andReturn($dompdf);

        $submission = app(HealthFormPdfSnapshotService::class)->recordSubmittedWithoutPdf(
            $profile,
            $stagedUser,
            'Academic Year 2026-2027',
            null,
            false,
            $stagedProfile
        );

        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $submission->status);
        $this->assertSame('New address', data_get($submission->fresh()->profile_snapshot, 'profile.home_address'));
        $this->assertSame('09222222222', data_get($submission->fresh()->profile_snapshot, 'user.contact_no'));
        $this->assertSame('Old address', $profile->fresh()->home_address);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('09111111111', $user->fresh()->contact_no);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $previousVersion->fresh()->status);
        $this->assertNotEmpty($previousVersion->fresh()->profile_snapshot);

        $admin = User::forceCreate(['name' => 'Clinic Admin']);
        $submission->forceFill([
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'approved_at' => now(),
        ])->save();

        app(HealthProfileSnapshotService::class)->promoteApprovedSubmission(
            $profile->fresh(),
            $submission->fresh(),
            $user->fresh(),
            $admin->id
        );

        $this->assertSame('New address', $profile->fresh()->home_address);
        $this->assertSame('Original assessment', $profile->fresh()->medical_condition_remarks);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('09222222222', $user->fresh()->contact_no);
        $this->assertSame('2005-02-12', $user->fresh()->DOB);
        $this->assertTrue((bool) $user->fresh()->is_health_profile_completed);
        $this->assertSame($admin->id, $profile->fresh()->approved_by_user_id);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $previousVersion->fresh()->status);
    }

    public function test_employee_snapshot_refresh_persists_new_path_and_removes_old_pdf(): void
    {
        Carbon::setTestNow('2026-07-28 15:45:00');

        $user = User::forceCreate([
            'name' => 'Test Employee',
            'employee_number' => 'EMP-100',
        ]);
        $oldPath = 'health_profile_employees/health_forms/old.pdf';
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id,
            'employee_number' => 'EMP-100',
            'uploaded_signature_path' => 'health_profile_employees/signatures/test.png',
            'staff_health_form_pdf_path' => $oldPath,
        ]);
        Storage::disk('health_private')->put($oldPath, 'old-pdf');

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('new-employee-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_employee_health_form', Mockery::on(function (array $data) use ($profile) {
                return (int) $data['employeeProfile']->id === (int) $profile->id
                    && $data['pdfMode'] === true;
            }))
            ->andReturn($dompdf);

        $newPath = app(EmployeeHealthFormPdfService::class)->generate($profile);
        $profile->refresh();

        $this->assertSame($newPath, $profile->staff_health_form_pdf_path);
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('health_private')->assertMissing($oldPath);
        $this->assertSame('new-employee-pdf', Storage::disk('health_private')->get($newPath));
    }

    public function test_stored_signature_is_converted_to_an_embedded_image_data_uri(): void
    {
        $path = 'health_profiles/signatures/test.png';
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        Storage::disk('public')->put($path, $contents);

        $source = app(StoredImageDataUri::class)->fromPublicDisk('storage/' . $path);

        $this->assertSame('data:image/png;base64,' . base64_encode($contents), $source);
    }

    public function test_student_form_prints_the_name_and_submission_date_without_a_signature(): void
    {
        $user = User::forceCreate([
            'name' => 'Marcella Mae Igcasenza Pada',
            'student_number' => '2026-0001',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0001',
        ])->load('user');

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => Carbon::parse('2026-07-20 09:00:00'),
        ])->render();

        $this->assertStringContainsString('MARCELLA MAE IGCASENZA PADA', $html);
        $this->assertStringContainsString('07/20/2026', $html);
        $this->assertStringContainsString('white-space: nowrap', $html);
        $this->assertStringContainsString('width: 190px', $html);
        $this->assertStringContainsString('font-size: 11px !important', $html);
    }

    public function test_home_address_value_starts_directly_after_its_label(): void
    {
        $user = (new User())->forceFill(['name' => 'Address Layout Student']);
        $profile = (new HealthProfile())->forceFill([
            'home_address' => '123 Sample Street, Taguig City',
        ]);
        $profile->setRelation('user', $user);

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
        ])->render();

        $this->assertMatchesRegularExpression(
            '/<span class="line-label home-address-label">Home Address:<\/span><span class="line-value home-address-value">123 Sample Street, Taguig City<\/span>/',
            $html
        );
        $this->assertStringContainsString('.home-address-label { display: inline-block; width: 72pt;', $html);
        $this->assertStringContainsString('.home-address-value { display: inline-block; width: 240pt;', $html);
    }

    public function test_very_long_student_name_automatically_uses_a_smaller_single_line_font(): void
    {
        $user = User::forceCreate([
            'name' => 'Alexandria Cassandra Maximiliana De Los Santos Evangelista',
            'student_number' => '2026-0002',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0002',
        ])->load('user');

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => Carbon::parse('2026-07-20 09:00:00'),
        ])->render();

        $this->assertMatchesRegularExpression(
            '/student-signature-name" style="font-size: (?:[4-9](?:\.\d+)?)px !important/',
            $html
        );
        $this->assertStringContainsString('white-space: nowrap', $html);
    }
}
