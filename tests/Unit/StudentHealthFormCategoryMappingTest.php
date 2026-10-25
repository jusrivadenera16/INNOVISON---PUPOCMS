<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\HealthFormCategoryController;
use App\Models\HealthFormCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class StudentHealthFormCategoryMappingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('health_form_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('available_for')->nullable();
            $table->json('student_types')->nullable();
            $table->timestamps();
        });
    }

    public function test_student_categories_are_audience_wide_and_ignore_legacy_subtype_mappings(): void
    {
        $category = HealthFormCategory::create([
            'name' => 'OJT Medical Clearance',
            'is_active' => true,
            'available_for' => ['student'],
            'student_types' => ['ojt'],
        ]);

        $controller = new AppointmentController();
        $availableMethod = new ReflectionMethod(AppointmentController::class, 'activeHealthFormCategories');
        $availableMethod->setAccessible(true);
        $availableForStudents = $availableMethod->invoke($controller, 'student');

        $this->assertSame([$category->id], $availableForStudents->modelKeys());

        $user = new User();
        $user->student_type = 'ojt';
        $resolvedMethod = new ReflectionMethod(AppointmentController::class, 'resolvedHealthFormCategory');
        $resolvedMethod->setAccessible(true);

        $this->assertSame('', $resolvedMethod->invoke($controller, $user, null, null, 'student'));
    }

    public function test_student_category_can_be_created_without_subtype_selection(): void
    {
        $request = Request::create('/admin/health-form-categories', 'POST', [
            'name' => 'OJT Medical Clearance',
            'audiences' => ['student'],
        ]);

        (new HealthFormCategoryController())->store($request);

        $category = HealthFormCategory::query()->firstOrFail();

        $this->assertSame('OJT Medical Clearance', $category->name);
        $this->assertSame(['student'], $category->available_for);
        $this->assertSame([], $category->student_types);
    }

    public function test_applicant_categories_are_not_used_for_transferee_workflows(): void
    {
        HealthFormCategory::create([
            'name' => 'Transfer Students',
            'is_active' => true,
            'available_for' => ['applicant'],
            'student_types' => [],
        ]);

        $user = new User();
        $user->applicant_type = 'transferee';

        $method = new ReflectionMethod(AppointmentController::class, 'resolvedHealthFormCategory');
        $method->setAccessible(true);

        $controller = new AppointmentController();
        $applicantCategory = $method->invoke($controller, $user, null, null, 'applicant');
        $studentCategory = $method->invoke($controller, $user, null, null, 'student');

        $this->assertSame('', $applicantCategory);
        $this->assertSame('', $studentCategory);
    }

    public function test_applicants_are_not_a_health_category_audience(): void
    {
        $this->assertArrayNotHasKey('applicant', HealthFormCategory::AUDIENCE_LABELS);
    }

    public function test_regular_consent_purpose_does_not_depend_on_a_health_form_category(): void
    {
        $method = new ReflectionMethod(AppointmentController::class, 'studentDeclarationPurposeText');
        $method->setAccessible(true);

        $declaration = $method->invoke(new AppointmentController(), 'Annual Medical Clearance', 'regular', 'student');

        $this->assertSame('currently enrolled student', $declaration['purpose']);
        $this->assertSame('status as a currently enrolled student', $declaration['endorsement']);
    }

    public function test_applicant_consent_purpose_uses_the_selected_new_or_transferee_type(): void
    {
        $method = new ReflectionMethod(AppointmentController::class, 'studentDeclarationPurposeText');
        $method->setAccessible(true);
        $controller = new AppointmentController();

        $newApplicant = $method->invoke($controller, 'Ignored Category', 'new', 'applicant');
        $transferee = $method->invoke($controller, 'Ignored Category', 'transferee', 'applicant');

        $this->assertSame('incoming freshman admission', $newApplicant['purpose']);
        $this->assertSame('transferee admission', $transferee['purpose']);
        $this->assertSame('transfer as a student', $transferee['endorsement']);
    }

    public function test_student_subtype_drives_consent_purpose_without_category_configuration(): void
    {
        $method = new ReflectionMethod(AppointmentController::class, 'studentDeclarationPurposeText');
        $method->setAccessible(true);

        $declaration = $method->invoke(new AppointmentController(), '', 'ladderized', 'student');

        $this->assertSame('Ladderized Program', $declaration['purpose']);
        $this->assertSame('Ladderized Program', $declaration['endorsement']);
    }
}
