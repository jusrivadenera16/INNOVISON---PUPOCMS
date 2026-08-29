<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AnnouncementImageUpdateTest extends TestCase
{
    public function test_existing_image_is_replaced_when_remove_indexes_are_provided(): void
    {
        File::ensureDirectoryExists(public_path('images/announcements'));

        $existingImage = UploadedFile::fake()->image('old-image.jpg');
        $existingImagePath = public_path('images/announcements/' . $existingImage->hashName());
        $existingImage->move(public_path('images/announcements'));

        $announcement = Announcement::create([
            'title' => 'Existing announcement',
            'priority' => 'info',
            'message' => '<p>Old message</p>',
            'show_on_landing' => true,
            'show_in_portal' => true,
            'image_paths' => ['announcements/' . basename($existingImagePath)],
            'status' => Announcement::STATUS_ACTIVE,
            'target_audience' => 'all',
        ]);

        $replacementImage = UploadedFile::fake()->image('new-image.png');

        $request = Request::create('/admin/announcements/' . $announcement->id, 'PATCH', [
            'title' => 'Updated announcement',
            'priority' => 'warning',
            'message' => '<p>Updated message</p>',
            'show_on_landing' => '1',
            'show_in_portal' => '1',
            'expires_at' => '',
            'remove_image_indexes' => [0],
        ]);
        $request->files->add(['images' => [$replacementImage]]);

        $controller = app(AdminController::class);
        $response = $controller->updateAnnouncement($request, $announcement);

        self::assertEquals(302, $response->getStatusCode());
        self::assertSame(1, count($announcement->fresh()->image_paths));
        self::assertStringContainsString('announcements/', $announcement->fresh()->image_paths[0]);
        self::assertStringNotContainsString('old-image', $announcement->fresh()->image_paths[0]);
        self::assertFileDoesNotExist($existingImagePath);
    }
}
