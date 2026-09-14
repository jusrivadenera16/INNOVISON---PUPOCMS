<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use App\Models\HealthFormCategory;
use Illuminate\Database\Schema\Blueprint;
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

    public function test_ojt_uses_the_configured_student_category(): void
    {
        $category = HealthFormCategory::create([
            'name' => 'On the Job Training',
            'is_active' => true,
            'available_for' => ['student'],
            'student_types' => ['ojt'],
        ]);

        $method = new ReflectionMethod(AppointmentController::class, 'studentHealthFormCategoryForType');
        $method->setAccessible(true);

        $resolved = $method->invoke(new AppointmentController(), 'ojt');

        $this->assertNotNull($resolved);
        $this->assertSame($category->id, $resolved->id);
    }

    public function test_regular_keeps_the_existing_declaration_wording(): void
    {
        $method = new ReflectionMethod(AppointmentController::class, 'studentDeclarationPurposeText');
        $method->setAccessible(true);

        $declaration = $method->invoke(new AppointmentController(), 'Student');

        $this->assertSame('currently enrolled student', $declaration['purpose']);
        $this->assertSame('status as a currently enrolled student', $declaration['endorsement']);
    }
}
