<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Models\ActivityLog;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HealthProfilePulloutWorkflowTest extends TestCase
{
    public function test_new_pullout_modal_does_not_repeat_restored_history_but_keeps_current_actions(): void
    {
        $source = file_get_contents(resource_path('views/admin/show_health.blade.php'));
        $start = strpos($source, '@if($canViewPullout)' . "\n" . '<div class="correction-modal"');
        if ($start === false) $start = strpos($source, '@if($canViewPullout)' . "\r\n" . '<div class="correction-modal"');
        $this->assertNotFalse($start);
        $end = strpos($source, '@if($profile->user?->clinicAccountTypeKey()', $start);
        $template = substr($source, $start, $end - $start);
        $profile = (new HealthProfile())->forceFill(['id' => 123, 'pullout_reason' => 'OLD PULLOUT REASON', 'pullout_request_remarks' => 'OLD REMARKS', 'pullout_restore_reason' => 'OLD RESTORE REASON']);
        $profile->setRelation('pulloutRequestedBy', null)->setRelation('pulloutCompletedBy', null);
        $data = ['profile' => $profile, 'canViewPullout' => true, 'isPulloutPending' => false, 'isPulledOut' => false,
            'isPulloutRestored' => true, 'isSuperAdmin' => true, 'canRequestPullout' => true,
            'pulloutActionLabel' => 'Mark as Pulled Out', 'pulloutValidationErrors' => new \Illuminate\Support\MessageBag()];
        $html = \Illuminate\Support\Facades\Blade::render($template, $data);
        $this->assertStringContainsString('id="pulloutReason"', $html);
        $this->assertStringContainsString('id="pulloutRequestRemarks"', $html);
        $this->assertStringNotContainsString('pullout-status-summary', $html);
        foreach (['OLD PULLOUT REASON', 'OLD REMARKS', 'OLD RESTORE REASON', 'The previous pullout was restored'] as $oldText) $this->assertStringNotContainsString($oldText, $html);
        $data['isPulloutRestored'] = false;
        $data['isPulloutPending'] = true;
        $pendingHtml = \Illuminate\Support\Facades\Blade::render($template, $data);
        $this->assertStringContainsString('pullout-status-summary', $pendingHtml);
        $this->assertStringContainsString(route('admin.health_profile.pullout.complete', 123), $pendingHtml);
        $data['isPulloutPending'] = false;
        $data['isPulledOut'] = true;
        $pulledHtml = \Illuminate\Support\Facades\Blade::render($template, $data);
        $this->assertStringContainsString('Restore Health Record', $pulledHtml);
        $this->assertStringContainsString(route('admin.health_profile.pullout.restore', 123), $pulledHtml);
    }

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
        config()->set('services.student_notifications.enabled', false);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('clearance_status')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action');
            $table->string('module')->nullable();
            $table->string('event_type')->nullable();
            $table->text('description')->nullable();
            $table->string('route_name')->nullable();
            $table->string('http_method')->nullable();
            $table->text('request_path')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        $migration = require database_path(
            'migrations/2026_08_24_030000_add_manual_pullout_workflow_to_health_profiles.php'
        );
        $migration->up();
        $accountStatusMigration = require database_path(
            'migrations/2026_08_24_040000_add_pullout_account_status_tracking_to_health_profiles.php'
        );
        $accountStatusMigration->up();

        Auth::shouldUse('admin');
    }

    public function test_manual_pullout_preserves_the_approved_record_and_supports_restore(): void
    {
        $owner = User::forceCreate([
            'name' => 'Pullout Record Owner',
            'email' => 'owner@example.test',
            'user_role' => User::ROLE_STUDENT,
        ]);
        $staff = User::forceCreate([
            'name' => 'Clinic Staff',
            'email' => 'staff@example.test',
            'user_role' => User::ROLE_ADMIN,
        ]);
        $superAdmin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $owner->id,
            'reference_number' => 'TEST-PULLOUT-001',
            'student_photo' => 'health_profiles/student_photos/original.jpg',
            'medical_certificate' => 'health_profiles/medical_certificates/original.pdf',
            'clearance_status' => 'Fully Cleared',
        ]);

        Auth::guard('admin')->login($staff);
        try {
            app(AdminController::class)->requestHealthProfilePullout(
                Request::create('/health-profile/' . $profile->id . '/pullout/request', 'POST', [
                    'pullout_reason' => 'Requested by the record owner',
                ]),
                $profile->id
            );
            $this->fail('Clinic staff should not be able to mark a health record as pulled out.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $profile->refresh();
        $this->assertNull($profile->pullout_status);
        $this->assertSame('Fully Cleared', $profile->clearance_status);
        $this->assertSame('health_profiles/student_photos/original.jpg', $profile->student_photo);
        $this->assertSame('health_profiles/medical_certificates/original.pdf', $profile->medical_certificate);

        Auth::guard('admin')->login($superAdmin);
        $completeResponse = app(AdminController::class)->requestHealthProfilePullout(
            Request::create('/health-profile/' . $profile->id . '/pullout/request', 'POST', [
                'pullout_reason' => 'Requested by the record owner',
                'pullout_request_remarks' => 'Manual pullout recorded by the Super Admin.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $completeResponse->getStatusCode());
        $profile->refresh();
        $this->assertSame(HealthProfile::PULLOUT_COMPLETED, $profile->pullout_status);
        $this->assertSame($superAdmin->id, $profile->pullout_completed_by_user_id);
        $this->assertSame('active', $profile->pullout_previous_user_status);
        $this->assertSame('Fully Cleared', $profile->clearance_status);
        $this->assertSame('health_profiles/student_photos/original.jpg', $profile->student_photo);
        $this->assertSame('inactive', $owner->fresh()->status);
        $this->assertFalse(HealthProfile::query()->notPulledOut()->whereKey($profile->id)->exists());
        $this->assertTrue(HealthProfile::query()->pulledOut()->whereKey($profile->id)->exists());

        $restoreResponse = app(AdminController::class)->restoreHealthProfilePullout(
            Request::create('/health-profile/' . $profile->id . '/pullout/restore', 'POST', [
                'pullout_restore_reason' => 'The record owner cancelled the pullout request.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $restoreResponse->getStatusCode());
        $profile->refresh();
        $this->assertSame(HealthProfile::PULLOUT_RESTORED, $profile->pullout_status);
        $this->assertSame('Fully Cleared', $profile->clearance_status);
        $this->assertSame('health_profiles/medical_certificates/original.pdf', $profile->medical_certificate);
        $this->assertSame('active', $owner->fresh()->status);
        $this->assertTrue(HealthProfile::query()->notPulledOut()->whereKey($profile->id)->exists());

        $directResponse = app(AdminController::class)->requestHealthProfilePullout(
            Request::create('/health-profile/' . $profile->id . '/pullout/request', 'POST', [
                'pullout_reason' => 'Other administrative reason',
            ]),
            $profile->id
        );

        $this->assertSame(302, $directResponse->getStatusCode());
        $profile->refresh();
        $this->assertSame(HealthProfile::PULLOUT_COMPLETED, $profile->pullout_status);
        $this->assertSame($superAdmin->id, $profile->pullout_completed_by_user_id);
        $this->assertSame('Fully Cleared', $profile->clearance_status);
        $this->assertSame('health_profiles/student_photos/original.jpg', $profile->student_photo);
        $this->assertSame('inactive', $owner->fresh()->status);
        $this->assertSame(3, ActivityLog::query()->where('subject_id', (string) $profile->id)->count());

        $otherRestoreResponse = app(AdminController::class)->restoreHealthProfilePullout(
            Request::create('/health-profile/' . $profile->id . '/pullout/restore', 'POST', [
                'pullout_restore_reason_option' => 'other',
                'pullout_restore_reason_other' => 'The account was reviewed and cleared for access.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $otherRestoreResponse->getStatusCode());
        $profile->refresh();
        $this->assertSame('Other: The account was reviewed and cleared for access.', $profile->pullout_restore_reason);
        $this->assertSame('active', $owner->fresh()->status);

        app(AdminController::class)->requestHealthProfilePullout(
            Request::create('/health-profile/' . $profile->id . '/pullout/request', 'POST', [
                'pullout_reason' => 'Requested by the record owner',
            ]),
            $profile->id
        );
        $this->assertSame('inactive', $owner->fresh()->status);
        $this->assertSame(5, ActivityLog::query()->where('subject_id', (string) $profile->id)->count());

        Auth::guard('admin')->logout();
        Auth::guard('student')->login($owner->fresh());

        $blockedRequest = Request::create('/student/account', 'GET');
        $blockedRequest->setLaravelSession(app('session.store'));
        $blockedResponse = app(EnsureAccountIsActive::class)->handle(
            $blockedRequest,
            fn () => response('allowed')
        );

        $this->assertSame(302, $blockedResponse->getStatusCode());
        $this->assertSame(url('/login?account_inactive=1'), $blockedResponse->getTargetUrl());
        $this->assertGuest('student');
    }
}
