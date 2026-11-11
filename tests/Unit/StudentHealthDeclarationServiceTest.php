<?php

namespace Tests\Unit;

use App\Models\{HealthProfile, User};
use App\Services\{HealthFileStorage, StudentHealthDeclarationService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentHealthDeclarationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Health model casts inspect schema; keep this check isolated from local MariaDB.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
    }

    public function test_new_category_wins_over_student_type_but_generic_forms_keep_the_fallback(): void
    {
        $service = app(StudentHealthDeclarationService::class);
        $this->assertSame('On-the-Job Training (OJT)', $service->purpose('OJT', 'ladderized')['purpose']);
        $this->assertSame('returning to school', $service->purpose('Return to School', 'ladderized')['purpose']);
        $this->assertSame('Annual / School Year Update', $service->purpose('Annual / School Year Update', 'ojt')['purpose']);
        $this->assertSame('Ladderized Program', $service->purpose('Student', 'ladderized')['purpose']);
        $this->assertSame('currently enrolled student', $service->purpose('General', 'regular')['purpose']);
    }

    public function test_declaration_generation_is_unique_and_uses_new_purpose_and_submission_date(): void
    {
        Storage::fake('health_private');
        $user = new User(['name' => 'Test Student', 'first_name' => 'Test', 'last_name' => 'Student', 'student_type' => 'ladderized']);
        $user->id = 123;
        $profile = new HealthProfile(['age' => 20, 'student_number' => 'TEST-123', 'health_declaration' => 'previous.pdf']);
        $files = app(HealthFileStorage::class);
        $files->put('previous.pdf', '%PDF-old-approved');
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->twice()->with('letter', 'portrait');
        $pdf->shouldReceive('output')->twice()->andReturn('%PDF-new-declaration');
        $captured = [];
        Pdf::shouldReceive('loadView')->twice()->with('student.print_declaration_form', \Mockery::on(function ($data) use (&$captured) { $captured = $data; return true; }))->andReturn($pdf);
        $service = app(StudentHealthDeclarationService::class);
        $date = Carbon::parse('2026-11-11 09:00:00', config('app.timezone'));
        $first = $service->generate($profile, $user, 'OJT', $date);
        $second = $service->generate($profile, $user, 'OJT', $date);
        $this->assertNotSame($first, $second);
        $this->assertTrue($files->exists($first));
        $this->assertTrue($files->exists($second));
        $this->assertSame('%PDF-old-approved', $files->get('previous.pdf'));
        $this->assertSame('previous.pdf', $profile->health_declaration);
        $this->assertSame('OJT', $captured['categorySelected']);
        $this->assertSame('11/11/2026', $captured['signatureDate']);
        $this->assertSame('On-the-Job Training (OJT)', $captured['purposeUnderline1']);
        $html = view('student.print_declaration_form', $captured)->render();
        $this->assertStringContainsString('<u>On-the-Job Training (OJT)</u>', $html);
        $this->assertStringNotContainsString('<u>Ladderized Program</u>', $html);
    }
}
