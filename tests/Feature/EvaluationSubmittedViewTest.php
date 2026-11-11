<?php

namespace Tests\Feature;

use App\Models\ConsultationEvaluation;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class EvaluationSubmittedViewTest extends TestCase
{
    public function test_submitted_evaluation_has_a_centered_home_link_below_the_message_without_reopening_the_form(): void
    {
        $evaluation = new ConsultationEvaluation(['status' => 'submitted', 'submitted_at' => now()]);
        $html = view('student.evaluation_form', ['evaluation' => $evaluation, 'errors' => new ViewErrorBag])->render();
        $this->assertStringContainsString('href="'.url('/').'" class="survey-button primary survey-return-link">Back to CareSync</a>', $html);
        $this->assertLessThan(strpos($html, 'class="survey-submitted-actions"'), strpos($html, 'class="survey-submitted"'));
        $this->assertStringNotContainsString('id="clientSatisfactionSurvey"', $html);
    }

    public function test_unsubmitted_preview_does_not_show_the_submitted_state_back_button(): void
    {
        $evaluation = new ConsultationEvaluation(['status' => 'preview']);
        $html = view('student.evaluation_form', ['evaluation' => $evaluation, 'isPreview' => true, 'errors' => new ViewErrorBag])->render();
        $this->assertStringNotContainsString('>Back to CareSync</a>', $html);
        $this->assertStringContainsString('id="clientSatisfactionSurvey"', $html);
    }
}
