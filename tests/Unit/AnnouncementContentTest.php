<?php

namespace Tests\Unit;

use App\Services\AnnouncementContent;
use PHPUnit\Framework\TestCase;

class AnnouncementContentTest extends TestCase
{
    public function test_it_renders_supported_announcement_formatting_without_allowing_raw_html(): void
    {
        $html = AnnouncementContent::toHtml("**Important** *update*\n- First item\n- Second item\n<script>alert('x')</script>");

        $this->assertStringContainsString('<strong>Important</strong>', $html);
        $this->assertStringContainsString('<em>update</em>', $html);
        $this->assertStringContainsString('<ul><li>First item</li><li>Second item</li></ul>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_it_builds_a_plain_text_preview_without_formatting_tokens(): void
    {
        $plainText = AnnouncementContent::toPlainText("**Important** *update*\n- First item");

        $this->assertSame("Important update\nFirst item", $plainText);
    }

    public function test_it_preserves_editor_formatting_without_accepting_attributes_or_scripts(): void
    {
        $message = '<div onclick="alert(1)"><strong>Clinic update</strong><br><em>Bring your ID.</em></div><script>alert(2)</script>';

        $html = AnnouncementContent::toHtml($message);

        $this->assertSame('<p><strong>Clinic update</strong><br><em>Bring your ID.</em></p>alert(2)', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_it_preserves_safe_links_and_rejects_unsafe_protocols(): void
    {
        $message = '<p><a href="https://clinic.example.test/path" onclick="alert(1)">Open clinic site</a> <a href="javascript:alert(1)">Unsafe</a></p>';

        $html = AnnouncementContent::toHtml($message);

        $this->assertSame(
            '<p><a href="https://clinic.example.test/path" target="_blank" rel="noopener noreferrer">Open clinic site</a> <a>Unsafe</a></p>',
            $html
        );
    }
}
