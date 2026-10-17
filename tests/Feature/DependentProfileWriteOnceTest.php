<?php

namespace Tests\Feature;

use App\Http\Controllers\AppointmentController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DependentProfileWriteOnceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('user_type')->nullable();
            $table->string('user_role')->nullable();
            $table->string('idp_role')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });

        Schema::create('dependents_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function test_direct_repeat_post_cannot_overwrite_an_existing_dependent_profile(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Dependent User',
            'email' => 'account@example.test',
            'password' => 'test',
            'user_type' => 'Dependent',
            'user_role' => 'student',
            'idp_role' => 'dependent',
            'student_id' => 'dependent-id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('dependents_profiles')->insert([
            'user_id' => $userId,
            'email' => 'original@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Auth::guard('student')->setUser(User::query()->findOrFail($userId));

        $response = app(AppointmentController::class)->storeDependentProfile(
            Request::create('/student/dependent-profile', 'POST')
        );

        $this->assertStringEndsWith('/student/account', $response->getTargetUrl());
        $this->assertSame(
            'original@example.test',
            DB::table('dependents_profiles')->where('user_id', $userId)->value('email')
        );
    }
}
