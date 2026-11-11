<?php

namespace Tests\Unit;

use App\Http\Controllers\WalkInController;
use App\Services\PuptasWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class WalkInLocalSisReferenceLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number')->nullable();
            $table->string('sis_reference_number')->nullable();
            $table->string('student_number')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('applicant_type')->nullable();
            $table->string('barcode')->nullable();
            $table->string('student_id')->nullable();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('sis_reference_number')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('student_number')->nullable();
            $table->string('student_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('health_profile_emp', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('health_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('dependents_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('user_role')->nullable();
            $table->string('action');
            $table->string('module')->nullable();
            $table->string('event_type')->nullable();
            $table->text('description');
            $table->string('route_name')->nullable();
            $table->string('http_method')->nullable();
            $table->string('request_path')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->text('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    public function test_it_finds_a_user_by_the_local_sis_reference(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'sis_reference_number' => '2026-0000-0001',
        ]);

        $user = $this->findUserByIdentifier('2026-0000-0001');

        $this->assertNotNull($user);
        $this->assertSame(1, $user->id);
    }

    public function test_it_returns_local_sis_matches_without_calling_puptas(): void
    {
        Http::fake();
        DB::table('users')->insert([
            'id' => 3,
            'sis_reference_number' => '2026-2222-3333',
            'applicant_type' => 'transferee',
            'name' => 'Local Test Applicant',
        ]);

        $request = Request::create(
            '/admin/walkin/get-student?student_id=2026-2222-3333&preview_only=true',
            'GET'
        );
        $response = app(WalkInController::class)->getStudent(
            $request,
            app(PuptasWebhookService::class)
        );
        $payload = $response->getData(true);

        $this->assertSame('preview', $payload['status']);
        $this->assertSame('local_sis_reference', $payload['lookup_source']);
        $this->assertSame('Local Test Applicant', $payload['student_name']);
        $this->assertNull($payload['applicant_photo_url']);
        Http::assertNothingSent();
    }

    public function test_it_falls_back_to_users_sis_reference_when_puptas_does_not_find_a_transferee(): void
    {
        $puptas = \Mockery::mock(PuptasWebhookService::class);
        $puptas->shouldReceive('fetchApplicantByReferenceNumberDetailed')
            ->once()
            ->with('2026-2121-1212')
            ->andReturn([
                'success' => false,
                'outcome' => 'not_found',
                'status' => 404,
                'message' => 'No eligible PUPTAS applicant was found for that reference number.',
                'data' => null,
            ]);

        DB::table('users')->insert([
            'id' => 5,
            'sis_reference_number' => '202621211212',
            'applicant_type' => 'transferee',
            'name' => 'Local Transferee',
        ]);

        $request = Request::create(
            '/admin/walkin/get-student?student_id=2026-2121-1212&preview_only=true',
            'GET'
        );
        $payload = app(WalkInController::class)->getStudent($request, $puptas)->getData(true);

        $this->assertSame('preview', $payload['status']);
        $this->assertSame('local_sis_reference', $payload['lookup_source']);
        $this->assertSame('transferee', $payload['applicant_type']);
        $this->assertSame('Local Transferee', $payload['student_name']);
    }

    public function test_it_reports_not_found_only_after_puptas_and_local_sis_are_checked(): void
    {
        $puptas = \Mockery::mock(PuptasWebhookService::class);
        $puptas->shouldReceive('fetchApplicantByReferenceNumberDetailed')
            ->once()
            ->with('2026-9999-0000')
            ->andReturn([
                'success' => false,
                'outcome' => 'not_found',
                'status' => 404,
                'message' => 'No eligible PUPTAS applicant was found for that reference number.',
                'data' => null,
            ]);

        $request = Request::create(
            '/admin/walkin/get-student?student_id=2026-9999-0000&preview_only=true',
            'GET'
        );
        $payload = app(WalkInController::class)->getStudent($request, $puptas)->getData(true);

        $this->assertSame('not_found', $payload['status']);
        $this->assertSame(
            'No applicant or transferee record was found in PUPTAS or local SIS records.',
            $payload['message']
        );
    }

    public function test_reference_lookup_returns_a_protected_photo_url_for_applicants_with_a_photo(): void
    {
        Http::fake();
        DB::table('users')->insert([
            'id' => 4,
            'sis_reference_number' => '2026-4444-5555',
            'name' => 'Applicant With Photo',
        ]);
        DB::table('health_profiles')->insert([
            'user_id' => 4,
            'sis_reference_number' => '2026-4444-5555',
            'student_photo' => 'health_profiles/applicant-photo.png',
        ]);

        $request = Request::create(
            '/admin/walkin/get-student?student_id=2026-4444-5555&preview_only=true',
            'GET'
        );
        $payload = app(WalkInController::class)->getStudent(
            $request,
            app(PuptasWebhookService::class)
        )->getData(true);

        $this->assertSame(
            route('walkin.applicant-photo', ['healthProfile' => 1]),
            $payload['applicant_photo_url']
        );
        Http::assertNothingSent();
    }

    public function test_it_finds_a_user_when_the_local_sis_reference_is_saved_on_the_health_profile(): void
    {
        DB::table('users')->insert(['id' => 2]);
        DB::table('health_profiles')->insert([
            'user_id' => 2,
            'sis_reference_number' => '2026-1111-2222',
        ]);

        $user = $this->findUserByIdentifier('2026-1111-2222');

        $this->assertNotNull($user);
        $this->assertSame(2, $user->id);
        $this->assertSame('2026-1111-2222', $user->healthProfile->sis_reference_number);
    }

    private function findUserByIdentifier(string $reference): mixed
    {
        $controller = new WalkInController();
        $method = new ReflectionMethod($controller, 'findUserByIdentifier');
        $method->setAccessible(true);

        return $method->invoke($controller, $reference);
    }
}
