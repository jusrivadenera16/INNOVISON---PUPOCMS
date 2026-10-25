<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\ClinicBranding;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClinicLogoUpdateTest extends TestCase
{
    private ?string $uploadedLogoPath = null;

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
        config()->set('app.system_developer_email', 'developer@example.test');
        Auth::shouldUse('admin');

        Schema::create('users', function (Blueprint $table): void {
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

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->timestamps();
        });

        ClinicBranding::forgetCachedLogoPath();

        $this->beforeApplicationDestroyed(function (): void {
            if ($this->uploadedLogoPath) {
                File::delete(public_path($this->uploadedLogoPath));
                $directory = public_path(dirname($this->uploadedLogoPath));
                if (is_dir($directory) && count(scandir($directory) ?: []) === 2) {
                    @rmdir($directory);
                }
            }
        });
    }

    public function test_superadmin_can_upload_and_apply_a_clinic_logo(): void
    {
        $developer = $this->developerAccount();
        $upload = UploadedFile::fake()->image('clinic-logo.png', 300, 300);

        $this->actingAs($developer, 'admin')
            ->withoutMiddleware()
            ->from(route('admin.developer-tools'))
            ->post(route('admin.developer-tools.clinic-logo.update'), ['clinic_logo' => $upload])
            ->assertRedirect(route('admin.developer-tools'))
            ->assertSessionHas('success', 'Clinic logo updated successfully.')
            ->assertSessionHas('open_developer_options', true);

        $this->uploadedLogoPath = (string) SystemSetting::getValue('clinic_logo_path');

        $this->assertMatchesRegularExpression('/\Auploads\/clinic-branding\/clinic-logo-[0-9a-f-]+\.png\z/i', $this->uploadedLogoPath);
        $this->assertFileExists(public_path($this->uploadedLogoPath));
        $this->assertSame($this->uploadedLogoPath, ClinicBranding::logoPath());
        $this->assertStringContainsString($this->uploadedLogoPath, ClinicBranding::logoUrl());
    }

    public function test_invalid_file_is_rejected_without_changing_the_saved_logo(): void
    {
        $developer = $this->developerAccount();
        SystemSetting::putValue('clinic_logo_path', 'uploads/clinic-branding/clinic-logo-12345678-1234-1234-1234-123456789abc.png');

        $this->actingAs($developer, 'admin')
            ->withoutMiddleware()
            ->from(route('admin.developer-tools'))
            ->post(route('admin.developer-tools.clinic-logo.update'), [
                'clinic_logo' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
            ])
            ->assertRedirect(route('admin.developer-tools'))
            ->assertSessionHasErrors('clinic_logo');

        $this->assertSame(
            'uploads/clinic-branding/clinic-logo-12345678-1234-1234-1234-123456789abc.png',
            SystemSetting::getValue('clinic_logo_path')
        );
    }

    private function developerAccount(): User
    {
        return User::forceCreate([
            'name' => 'System Developer',
            'email' => 'developer@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'status' => 'active',
        ]);
    }
}
