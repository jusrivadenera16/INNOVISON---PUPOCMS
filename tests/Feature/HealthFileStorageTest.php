<?php

namespace Tests\Feature;

use App\Services\HealthFileStorage;
use App\Services\StoredImageDataUri;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthFileStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('health_private');
        Storage::fake('public');
        config([
            'health_files.write_disk' => 'health_private',
            'health_files.legacy_disk' => 'public',
            'health_files.legacy_fallback' => true,
            'health_files.mirror_to_legacy' => false,
            'health_files.delete_legacy_on_replace' => false,
            'health_files.reference_fields' => [],
        ]);
    }

    public function test_private_file_takes_precedence_over_legacy_file(): void
    {
        $path = 'health_profiles/documents/test.pdf';
        Storage::disk('public')->put($path, 'legacy');
        Storage::disk('health_private')->put($path, 'private');

        $this->assertSame('private', app(HealthFileStorage::class)->get($path));
    }

    public function test_legacy_file_remains_readable_during_transition(): void
    {
        $path = 'health_profiles/documents/legacy.pdf';
        Storage::disk('public')->put($path, 'legacy');

        $files = app(HealthFileStorage::class);

        $this->assertTrue($files->exists('storage/' . $path));
        $this->assertSame('legacy', $files->get($path));
    }

    public function test_legacy_file_is_not_read_when_fallback_is_disabled(): void
    {
        $path = 'health_profiles/documents/legacy.pdf';
        Storage::disk('public')->put($path, 'legacy');
        config(['health_files.legacy_fallback' => false]);

        $this->assertFalse(app(HealthFileStorage::class)->exists($path));
    }

    public function test_new_file_is_written_only_to_private_storage_by_default(): void
    {
        $path = 'health_profiles/documents/private.pdf';

        $this->assertTrue(app(HealthFileStorage::class)->put($path, 'private-data'));
        Storage::disk('health_private')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_mirroring_can_be_enabled_during_a_staged_rollout(): void
    {
        $path = 'health_profiles/documents/mirrored.pdf';
        config(['health_files.mirror_to_legacy' => true]);

        $this->assertTrue(app(HealthFileStorage::class)->put($path, 'mirrored-data'));
        $this->assertSame('mirrored-data', Storage::disk('health_private')->get($path));
        $this->assertSame('mirrored-data', Storage::disk('public')->get($path));
    }

    public function test_deleting_during_transition_preserves_legacy_copy(): void
    {
        $path = 'health_profiles/documents/replaced.pdf';
        Storage::disk('public')->put($path, 'legacy');
        Storage::disk('health_private')->put($path, 'private');

        app(HealthFileStorage::class)->delete($path);

        Storage::disk('health_private')->assertMissing($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_or_traversing_paths_are_rejected(): void
    {
        $files = app(HealthFileStorage::class);

        $this->assertSame('', $files->normalizePath('../../secret.pdf'));
        $this->assertSame('', $files->normalizePath('https://example.test/file.pdf'));
        $this->assertSame('', $files->normalizePath('C:\private\secret.pdf'));
        $this->assertFalse($files->exists('../../secret.pdf'));
    }

    public function test_pdf_image_helper_reads_a_private_image(): void
    {
        $path = 'health_profiles/signatures/test.png';
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        Storage::disk('health_private')->put($path, $contents);

        $source = app(StoredImageDataUri::class)->fromStorage($path);

        $this->assertSame('data:image/png;base64,' . base64_encode($contents), $source);
    }

    public function test_migration_command_copies_verifies_and_preserves_legacy_files(): void
    {
        Storage::disk('public')->put('health_profiles/photos/one.png', 'one');
        Storage::disk('public')->put('health_form_submissions/1/form.pdf', 'two');

        $exitCode = Artisan::call('health-files:migrate-private');

        $this->assertSame(0, $exitCode);
        Storage::disk('health_private')->assertExists('health_profiles/photos/one.png');
        Storage::disk('health_private')->assertExists('health_form_submissions/1/form.pdf');
        Storage::disk('public')->assertExists('health_profiles/photos/one.png');
        Storage::disk('public')->assertExists('health_form_submissions/1/form.pdf');

        $this->assertSame(0, Artisan::call('health-files:migrate-private'));
    }

    public function test_migration_dry_run_does_not_copy_files(): void
    {
        $path = 'health_profiles/photos/dry-run.png';
        Storage::disk('public')->put($path, 'image');

        $this->assertSame(0, Artisan::call('health-files:migrate-private', ['--dry-run' => true]));
        Storage::disk('health_private')->assertMissing($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_migration_does_not_overwrite_a_conflicting_private_file(): void
    {
        $path = 'health_profiles/photos/conflict.png';
        Storage::disk('public')->put($path, 'legacy-image');
        Storage::disk('health_private')->put($path, 'different-private-image');

        $this->assertSame(1, Artisan::call('health-files:migrate-private'));
        $this->assertSame('different-private-image', Storage::disk('health_private')->get($path));
        $this->assertSame('legacy-image', Storage::disk('public')->get($path));
    }
}
