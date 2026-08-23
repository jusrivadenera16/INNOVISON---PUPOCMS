<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminUserController;
use App\Services\FacultySyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AdminHubLookupSourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.admin_hub_lookup_test', array_merge(
            config('database.connections.sqlite'),
            ['database' => ':memory:']
        ));
        Config::set('database.default', 'admin_hub_lookup_test');
        DB::purge('admin_hub_lookup_test');
        DB::reconnect('admin_hub_lookup_test');
        DB::connection()->getPdo()->sqliteCreateFunction(
            'CONCAT_WS',
            function ($separator, ...$values) {
                return implode($separator, array_filter($values, fn ($value) => $value !== null && $value !== ''));
            },
            -1
        );

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('user_role')->nullable();
            $table->string('idp_role')->nullable();
            $table->string('user_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('email_address')->nullable();
            $table->string('office')->nullable();
            $table->string('status')->nullable();
            $table->string('access_level')->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_hub', function (Blueprint $table) {
            $table->id();
            $table->string('admin_uuid')->nullable()->unique();
            $table->string('employee_number')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('office')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Config::set('services.pupt_flss.faculty_profiles_url', 'https://faculty.example.test/api/faculties');
        Config::set('services.pupt_flss.secret_key', 'faculty-test-secret');
        Config::set('services.pupt_flss.timeout', 3);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admin_hub');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_a_standalone_admin_profile_stays_on_the_add_to_admin_hub_flow(): void
    {
        DB::table('admins')->insert([
            'admin_id' => 50,
            'employee_number' => 'FA-0050',
            'name' => 'Standalone Admin',
            'email' => 'standalone.admin@example.test',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'https://faculty.example.test/api/faculties*' => Http::response(['faculties' => []]),
        ]);

        $request = Request::create('/admin/user-management/admin-hub', 'GET', [
            'lookup_search' => 'Standalone',
            'management_view' => 'admin-hub',
        ]);
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'buildManagementData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $request, app(FacultySyncService::class), 'admin-hub');
        $record = collect($data['lookupRecords'])->firstWhere('source', 'admin_profile');

        $this->assertNotNull($record);
        $this->assertFalse($record['can_edit']);
        $this->assertTrue($record['can_onboard']);
        $this->assertFalse($record['is_local_user']);
    }

    public function test_a_matching_faculty_record_enriches_the_admin_profile_without_creating_a_duplicate(): void
    {
        DB::table('admins')->insert([
            'admin_id' => 51,
            'employee_number' => 'FA0010TG2023',
            'name' => 'Rhyan Molinar',
            'email' => 'rvmolinar@pup.edu.ph',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $facultyUuid = '4f9ac4c0-61f5-43f4-b9b4-9a89482091a7';

        Http::fake([
            'https://faculty.example.test/api/faculties*' => Http::response([
                'faculties' => [[
                    'identifier' => 'FA0010TG2023',
                    'name' => 'Rhyan Molinar',
                    'fields' => [
                        'faculty_code' => 'FA0010TG2023',
                        'idp_user_id' => $facultyUuid,
                        'email' => 'rvmolinar@pup.edu.ph',
                    ],
                ]],
            ]),
        ]);

        $request = Request::create('/admin/user-management/admin-hub', 'GET', [
            'lookup_search' => 'Rhyan',
            'management_view' => 'admin-hub',
        ]);
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'buildManagementData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $request, app(FacultySyncService::class), 'admin-hub');
        $matches = collect($data['lookupRecords'])
            ->where('email', 'rvmolinar@pup.edu.ph')
            ->values();

        $this->assertCount(1, $matches);
        $this->assertSame('admin_profile', $matches->first()['source']);
        $this->assertSame($facultyUuid, $matches->first()['meta']['admin_uuid']);
        $this->assertSame($facultyUuid, $matches->first()['meta']['idp_user_id']);
    }

    public function test_an_existing_admin_hub_record_receives_the_matching_faculty_uuid_as_a_display_and_save_fallback(): void
    {
        DB::table('admin_hub')->insert([
            'employee_number' => 'FA0010TG2023',
            'name' => 'Rhyan Molinar',
            'email' => 'rvmolinar@pup.edu.ph',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $facultyUuid = '4f9ac4c0-61f5-43f4-b9b4-9a89482091a7';

        Http::fake([
            'https://faculty.example.test/api/faculties*' => Http::response([
                'faculties' => [[
                    'identifier' => 'FA0010TG2023',
                    'fields' => [
                        'faculty_code' => 'FA0010TG2023',
                        'idp_user_id' => $facultyUuid,
                        'email' => 'rvmolinar@pup.edu.ph',
                    ],
                ]],
            ]),
        ]);

        $request = Request::create('/admin/user-management/admin-hub', 'GET', [
            'management_view' => 'admin-hub',
        ]);
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'buildManagementData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $request, app(FacultySyncService::class), 'admin-hub');
        $record = collect($data['adminHubRecords'])->firstWhere('email', 'rvmolinar@pup.edu.ph');

        $this->assertNotNull($record);
        $this->assertSame($facultyUuid, $record['meta']['admin_uuid']);
        $this->assertDatabaseHas('admin_hub', [
            'email' => 'rvmolinar@pup.edu.ph',
            'admin_uuid' => null,
        ]);
    }
}
