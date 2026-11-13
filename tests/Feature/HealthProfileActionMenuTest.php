<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class HealthProfileActionMenuTest extends TestCase
{
    private function renderMenu(bool $canPullout = true, bool $canReturn = true, bool $canConsult = true): string
    {
        $source = file_get_contents(resource_path('views/admin/show_health.blade.php'));
        $start = strpos($source, '<div class="profile-actions-menu" id="profileActionsMenu">');
        $end = strpos($source, '</div>', $start) + strlen('</div>');
        return Blade::render(substr($source, $start, $end - $start), [
            'canManageNewHealthForm' => true, 'canViewExistingNewHealthFormRequest' => false,
            'canRequestFileCorrection' => true, 'canUpdateMedicalAssessment' => true, 'canSendRecordEvaluation' => true,
            'canViewPullout' => $canPullout, 'pulloutActionLabel' => 'Mark as Pulled Out', 'canRevokeClearance' => $canReturn,
            'canStartConsultation' => $canConsult,
        ]);
    }

    public function test_pullout_and_return_to_pending_are_the_final_two_actions_with_maroon_styling(): void
    {
        $html = $this->renderMenu();
        $history = strpos($html, 'data-health-record-history-open');
        $this->assertTrue(strpos($html, 'data-record-consult-open') < $history);
        $pullout = strpos($html, 'id="openPulloutRequestModal"');
        $return = strpos($html, 'id="openReturnToPendingModal"');
        $this->assertTrue($history < $pullout && $pullout < $return);
        $this->assertSame(2, substr_count($html, 'class="profile-action-critical"'));
        $this->assertStringContainsString('Return to Pending', $html);
        $this->assertStringNotContainsString('Revoke Clearance', $html);
        $source = file_get_contents(resource_path('views/admin/show_health.blade.php'));
        $this->assertStringContainsString('#profileActionsMenu button.profile-action-critical {', $source);
        $this->assertStringContainsString('<h3 id="returnPendingTitle">Return to Pending</h3>', $source);
        $this->assertStringContainsString("confirm: 'Return to Pending'", $source);
        $this->assertStringContainsString("route('admin.health_profile.revoke_clearance', \$profile->id)", $source);
    }

    public function test_existing_permission_conditions_still_hide_unavailable_actions(): void
    {
        $html = $this->renderMenu(false, false, false);
        $this->assertStringNotContainsString('data-record-consult-open', $html);
        $this->assertStringNotContainsString('id="openPulloutRequestModal"', $html);
        $this->assertStringNotContainsString('id="openReturnToPendingModal"', $html);
        $this->assertStringContainsString('data-health-record-history-open', $html);
        $this->assertStringContainsString('Request File Correction', $html);
    }

    public function test_critical_actions_are_compact_without_icons_and_all_menu_items_have_sweep_hover(): void
    {
        $html = $this->renderMenu();
        $this->assertStringNotContainsString('profile-action-icon', $html);
        preg_match_all('/<button[^>]+class="profile-action-critical"[^>]*>(.*?)<\/button>/s', $html, $critical);
        $this->assertCount(2, $critical[1]);
        foreach ($critical[1] as $button) $this->assertStringNotContainsString('<svg', $button);
        $this->assertStringNotContainsString('&minus;', $html);
        $this->assertStringNotContainsString('&crarr;', $html);
        $source = file_get_contents(resource_path('views/admin/show_health.blade.php'));
        $start = strpos($source, '#profileActionsMenu button.profile-action-critical {');
        $end = strpos($source, '    .profile-tab {', $start);
        $css = substr($source, $start, $end - $start);
        foreach (['background: #8B0000;', 'background: #facc15;', 'justify-content: flex-start;',
            'min-height: 40px;', 'font-size: 12px;', '#profileActionsMenu button::before {',
            '#profileActionsMenu button:hover::before,', '#profileActionsMenu button:focus-visible::before {',
            'left: 112%;', 'prefers-reduced-motion: reduce'] as $rule) {
            $this->assertStringContainsString($rule, $css);
        }
    }
}
