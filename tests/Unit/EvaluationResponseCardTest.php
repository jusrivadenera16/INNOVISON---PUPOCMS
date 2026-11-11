<?php

namespace Tests\Unit;

use App\Models\ConsultationEvaluation;
use App\Models\User;
use Tests\TestCase;

class EvaluationResponseCardTest extends TestCase
{
    private function card(?string $suggestion, ?array $name): string
    {
        $user = null;
        if ($name !== null) {
            $user = new User;
            $user->setRawAttributes($name);
        }
        $evaluation = new ConsultationEvaluation([
            'source_type' => 'student_approval', 'rating' => 5,
            'suggestions' => $suggestion, 'submitted_at' => '2026-11-11 14:00:00',
        ]);
        $evaluation->setRelation('user', $user);
        $evaluation->setRelation('consultation', null);

        return view('admin.reports.partials.evaluation-response-card', compact('evaluation'))->render();
    }

    public function test_empty_suggestions_do_not_hide_the_rating_or_leave_a_placeholder(): void
    {
        foreach ([null, '', '   '] as $suggestion) {
            $html = $this->card($suggestion, ['first_name' => 'Juan Miguel', 'last_name' => 'Dela Cruz']);
            $this->assertStringContainsString('Juan Miguel D.', $html);
            $this->assertStringNotContainsString('Dela Cruz', $html);
            $this->assertStringContainsString('5/5', $html);
            $this->assertStringContainsString('Student Health Form Review', $html);
            $this->assertStringNotContainsString('evaluation-message', $html);
            $this->assertStringNotContainsString('No optional suggestion', $html);
        }
    }

    public function test_written_suggestions_are_still_displayed_and_escaped(): void
    {
        $html = $this->card('<script>Test</script>', ['name' => 'Maria Santos']);
        $this->assertStringContainsString('Maria S.', $html);
        $this->assertStringContainsString('evaluation-message', $html);
        $this->assertStringContainsString('&lt;script&gt;Test&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('Maria Santos', $html);
    }

    public function test_missing_account_has_a_neutral_display_name(): void
    {
        $this->assertStringContainsString('Clinic User', $this->card(null, null));
    }
}
