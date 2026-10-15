<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\User;
use App\Services\AdminAppointmentAlertMailer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAppointmentAlertMailerTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        Config::set('database.default', 'sqlite');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('status')->nullable();
            $table->boolean('admin_appointment_email_alerts')->default(false);
            $table->json('module_permissions')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        Config::set('database.default', $this->originalDefaultConnection);

        parent::tearDown();
    }

    public function test_only_opted_in_admin_accounts_with_appointment_access_receive_alerts(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'admin_appointment_email_alerts' => true,
        ]);
        User::create([
            'name' => 'Student',
            'email' => 'student@example.test',
            'user_role' => User::ROLE_STUDENT,
            'admin_appointment_email_alerts' => true,
        ]);
        User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'status' => 'inactive',
            'admin_appointment_email_alerts' => true,
        ]);

        $appointment = new Appointment([
            'apt_id' => 'OAPT-151026-100001',
            'name' => 'Test Patient',
            'service' => 'General Consultation',
            'date' => '2026-10-16',
            'time' => '10:00:00',
            'status' => 'Pending',
        ]);

        app(AdminAppointmentAlertMailer::class)->sendNewAppointmentAlert($appointment);

        Mail::assertSent(\App\Mail\AdminAppointmentAlertMail::class, function ($mail): bool {
            return isset($mail->to[0]['address']) && $mail->to[0]['address'] === 'admin@example.test';
        });
        Mail::assertSent(\App\Mail\AdminAppointmentAlertMail::class, 1);
    }

    public function test_admin_email_alerts_are_disabled_by_default(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
        ]);

        app(AdminAppointmentAlertMailer::class)->sendNewAppointmentAlert(new Appointment([
            'apt_id' => 'OAPT-151026-100001',
            'name' => 'Test Patient',
            'service' => 'General Consultation',
            'date' => '2026-10-16',
            'time' => '10:00:00',
            'status' => 'Pending',
        ]));

        Mail::assertNothingSent();
    }
}
