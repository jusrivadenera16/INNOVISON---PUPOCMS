<?php

namespace Tests\Feature;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\EmployeeHealthFormPdfService;
use App\Services\HealthFormPdfSnapshotService;
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
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('school_year')->nullable();
            $table->string('digital_signature')->nullable();
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
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
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
