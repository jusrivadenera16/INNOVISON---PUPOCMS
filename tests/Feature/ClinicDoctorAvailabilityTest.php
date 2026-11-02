<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Services\ClinicWorkflowService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClinicDoctorAvailabilityTest extends TestCase
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

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('open_time')->nullable();
            $table->string('close_time')->nullable();
            $table->json('operating_days')->nullable();
            $table->boolean('clinic_closure_enabled')->default(false);
            $table->dateTime('clinic_closure_starts_at')->nullable();
            $table->dateTime('clinic_closure_ends_at')->nullable();
            $table->string('clinic_closure_reason')->nullable();
            $table->text('clinic_closure_message')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('user_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->increments('admin_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('email_address')->nullable();
            $table->string('name')->nullable();
            $table->string('report_position')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('pullout_status')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_doctor_is_unavailable_by_default_without_an_assigned_doctor(): void
    {
        $this->assertFalse(app(ClinicWorkflowService::class)->doctorIsAvailable());
    }

    public function test_doctor_availability_is_limited_to_the_day_it_was_set(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $this->createAccountWithPosition('admin', 'Doctor');
        $workflow = app(ClinicWorkflowService::class);

        $workflow->setDoctorAvailability(true);
        $this->assertTrue($workflow->doctorIsAvailable());

        Carbon::setTestNow(Carbon::parse('2026-11-03 10:00:00', config('app.timezone')));
        $this->assertFalse($workflow->doctorIsAvailable());
    }

    public function test_doctor_account_login_marks_clinic_availability_as_available(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $doctor = $this->createAccountWithPosition('admin', 'Doctor');

        event(new Login('admin', $doctor, false));

        $this->assertTrue(app(ClinicWorkflowService::class)->doctorIsAvailable());
    }

    public function test_doctor_login_does_not_turn_availability_on_during_a_clinic_closure(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $doctor = $this->createAccountWithPosition('admin', 'Doctor');
        DB::table('settings')->insert([
            'open_time' => '08:00',
            'close_time' => '17:00',
            'operating_days' => json_encode([1, 2, 3, 4, 5]),
            'clinic_closure_enabled' => true,
            'clinic_closure_starts_at' => '2026-11-02 09:00:00',
            'clinic_closure_ends_at' => '2026-11-02 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        SystemSetting::putValue('clinic_doctor_available', '1');
        SystemSetting::putValue('clinic_doctor_availability_date', '2026-11-02');

        event(new Login('admin', $doctor, false));

        $this->assertFalse(app(ClinicWorkflowService::class)->doctorIsAvailable());
        $this->assertSame('0', SystemSetting::getValue('clinic_doctor_available'));
    }

    public function test_availability_stays_off_after_a_scheduled_closure_ends(): void
    {
        $doctor = $this->createAccountWithPosition('admin', 'Doctor');
        DB::table('settings')->insert([
            'open_time' => '08:00',
            'close_time' => '17:00',
            'operating_days' => json_encode([1, 2, 3, 4, 5]),
            'clinic_closure_enabled' => true,
            'clinic_closure_starts_at' => '2026-11-02 09:00:00',
            'clinic_closure_ends_at' => '2026-11-02 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $workflow = app(ClinicWorkflowService::class);
        $workflow->setDoctorAvailability(true, Carbon::parse('2026-11-02 08:30:00', config('app.timezone')));

        Carbon::setTestNow(Carbon::parse('2026-11-02 13:00:00', config('app.timezone')));

        $this->assertFalse($workflow->doctorIsAvailable());
        $this->assertSame('0', SystemSetting::getValue('clinic_doctor_available'));
    }

    public function test_non_doctor_and_student_assistant_logins_do_not_change_availability(): void
    {
        SystemSetting::putValue('clinic_doctor_available', '0');

        event(new Login('admin', $this->createAccountWithPosition('admin', 'Nurse'), false));
        $this->assertFalse(app(ClinicWorkflowService::class)->doctorIsAvailable());

        event(new Login('admin', $this->createAccountWithPosition('admin', 'Doctor', 'Assistant'), false));
        $this->assertFalse(app(ClinicWorkflowService::class)->doctorIsAvailable());
    }

    public function test_logging_out_does_not_turn_doctor_availability_off(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $doctor = $this->createAccountWithPosition('admin', 'Doctor');
        app(ClinicWorkflowService::class)->setDoctorAvailability(true);

        event(new Logout('admin', $doctor));

        $this->assertTrue(app(ClinicWorkflowService::class)->doctorIsAvailable());
    }

    public function test_landing_availability_endpoint_returns_the_current_public_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $this->createAccountWithPosition('admin', 'Doctor');

        $this->getJson(route('landing.doctor-availability'))
            ->assertOk()
            ->assertJsonStructure(['available', 'clinic_closed'])
            ->assertJson(['available' => false]);
    }

    public function test_landing_availability_endpoint_reports_an_active_clinic_closure(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', config('app.timezone')));
        $this->createAccountWithPosition('admin', 'Doctor');
        SystemSetting::putValue('clinic_doctor_available', '1');
        SystemSetting::putValue('clinic_doctor_availability_date', '2026-11-02');
        DB::table('settings')->insert([
            'open_time' => '08:00',
            'close_time' => '17:00',
            'operating_days' => json_encode([1, 2, 3, 4, 5, 6, 7]),
            'clinic_closure_enabled' => true,
            'clinic_closure_starts_at' => '2026-11-02 09:00:00',
            'clinic_closure_ends_at' => '2026-11-02 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(route('landing.doctor-availability'))
            ->assertOk()
            ->assertJson([
                'available' => false,
                'clinic_closed' => true,
            ]);

        $this->assertSame('0', SystemSetting::getValue('clinic_doctor_available'));
    }

    public function test_landing_availability_endpoint_reports_closed_outside_operating_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-03 10:00:00', config('app.timezone')));
        $this->createAccountWithPosition('admin', 'Doctor');
        SystemSetting::putValue('clinic_doctor_available', '1');
        SystemSetting::putValue('clinic_doctor_availability_date', '2026-11-03');
        DB::table('settings')->insert([
            'open_time' => '08:00',
            'close_time' => '17:00',
            'operating_days' => json_encode([1, 3, 5]),
            'clinic_closure_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(route('landing.doctor-availability'))
            ->assertOk()
            ->assertJson([
                'available' => false,
                'clinic_closed' => true,
            ]);
    }

    public function test_only_the_assigned_doctor_and_superadmins_can_manage_availability(): void
    {
        $workflow = app(ClinicWorkflowService::class);
        $doctor = $this->createAccountWithPosition('admin', 'Doctor');
        $regularAdmin = $this->createAccountWithPosition('admin', 'Nurse');
        $superadmin = $this->createAccountWithPosition('superadmin', 'Administrator');
        $assistant = $this->createAccountWithPosition('admin', 'Doctor', 'Assistant');

        $this->assertTrue($workflow->canManageDoctorAvailability($doctor));
        $this->assertTrue($workflow->canManageDoctorAvailability($superadmin));
        $this->assertFalse($workflow->canManageDoctorAvailability($regularAdmin));
        $this->assertFalse($workflow->canManageDoctorAvailability($assistant));

        $this->actingAs($regularAdmin, 'admin')
            ->post(route('admin.doctor-availability.update'), ['available' => '1'])
            ->assertForbidden();
    }

    private function createAccountWithPosition(string $role, string $position, string $userType = 'Regular'): User
    {
        $email = strtolower(str_replace(' ', '-', $role . '-' . $position . '-' . $userType)) . '@example.test';
        $userId = DB::table('users')->insertGetId([
            'name' => ucfirst($position) . ' User',
            'email' => $email,
            'user_role' => $role,
            'user_type' => $userType,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->insert([
            'user_id' => $userId,
            'email' => $email,
            'email_address' => $email,
            'name' => ucfirst($position) . ' User',
            'report_position' => $position,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($userId);
    }
}
