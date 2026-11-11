<?php

namespace Tests\Unit;

use Tests\TestCase;

class PendingHealthFormPurposeBadgeTest extends TestCase
{
    private function badge(string $profileCategory, ?string $activeCategory, string $userType = 'Student'): string
    {
        return view('admin.partials.pending-health-form-user-badge', [
            'record' => (object) ['id' => 42, 'health_form_category' => $profileCategory],
            'pendingHealthFormCategories' => collect($activeCategory === null ? [] : [42 => $activeCategory]),
            'resolveHealthRecordUserType' => fn ($record) => $userType,
        ])->render();
    }

    public function test_active_form_category_overrides_the_old_profile_purpose(): void
    {
        $ojt = $this->badge('Ladderized', 'OJT');
        $this->assertStringContainsString('Student · OJT', $ojt);
        $this->assertStringContainsString('is-purpose-ojt', $ojt);
        $this->assertStringNotContainsString('Student · Ladderized', $ojt);
        $ladderized = $this->badge('OJT', 'Ladderized Program');
        $this->assertStringContainsString('Student · Ladderized', $ladderized);
        $this->assertStringContainsString('is-purpose-ladderized', $ladderized);
    }

    public function test_general_and_non_student_forms_do_not_receive_student_purpose_badges(): void
    {
        $regular = $this->badge('OJT', 'Annual / School Year Update');
        $this->assertStringContainsString('>Student</span>', $regular);
        $this->assertStringNotContainsString('is-purpose-', $regular);
        $faculty = $this->badge('OJT', 'OJT', 'Faculty');
        $this->assertStringContainsString('>Faculty</span>', $faculty);
        $this->assertStringNotContainsString('is-purpose-', $faculty);
    }

    public function test_legacy_forms_and_full_ojt_name_are_supported_and_purpose_is_escaped(): void
    {
        $this->assertStringContainsString('Student · OJT', $this->badge('OJT', null));
        $this->assertStringContainsString('Student · OJT', $this->badge('Student', 'On-the-Job Training (OJT)'));
        $escaped = $this->badge('Student', 'OJT <script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }
}
