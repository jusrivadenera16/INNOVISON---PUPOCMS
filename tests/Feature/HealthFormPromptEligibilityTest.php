<?php

namespace Tests\Feature;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HealthFormPromptEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        foreach (['health_profiles', 'health_profile_emp', 'dependents_profiles'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }
    }

    public function test_any_existing_health_form_profile_suppresses_the_welcome_prompt(): void
    {
        $profiles = [
            ['health_profiles', 'healthProfile', HealthProfile::class],
            ['health_profile_emp', 'employeeHealthProfile', EmployeeHealthProfile::class],
            ['dependents_profiles', 'dependentProfile', DependentsProfile::class],
        ];

        foreach ($profiles as [$tableName, $relationName, $profileClass]) {
            $user = (new User())->forceFill(['is_health_profile_completed' => false]);
            $user->setRelation('healthProfile', null);
            $user->setRelation('employeeHealthProfile', null);
            $user->setRelation('dependentProfile', null);
            $user->setRelation($relationName, new $profileClass());

            $this->assertTrue(
                $user->hasExistingClinicHealthRecord(),
                "An existing {$tableName} profile must suppress the welcome prompt."
            );
        }
    }

    public function test_new_user_without_any_health_form_profile_still_needs_the_prompt(): void
    {
        $user = (new User())->forceFill(['is_health_profile_completed' => false]);
        $user->setRelation('healthProfile', null);
        $user->setRelation('employeeHealthProfile', null);
        $user->setRelation('dependentProfile', null);

        $this->assertFalse($user->hasExistingClinicHealthRecord());
    }
}
