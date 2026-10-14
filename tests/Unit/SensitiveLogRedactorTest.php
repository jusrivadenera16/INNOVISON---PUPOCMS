<?php

namespace Tests\Unit;

use App\Support\SensitiveLogRedactor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SensitiveLogRedactorTest extends TestCase
{
    public function test_masks_identifiers_while_preserving_useful_edges_and_formatting(): void
    {
        $this->assertSame('2026-****-4567', SensitiveLogRedactor::identifier('2026-0123-4567'));
        $this->assertSame('***-**12', SensitiveLogRedactor::identifier('EMP-0012'));
    }

    public function test_masks_email_local_part_and_keeps_domain(): void
    {
        $this->assertSame('em**@gmail.com', SensitiveLogRedactor::email('emai@gmail.com'));
        $this->assertSame('**@example.org', SensitiveLogRedactor::email('e@example.org'));
    }

    public function test_reduces_names_to_initials(): void
    {
        $this->assertSame('J.D.C.', SensitiveLogRedactor::name('Juan Dela Cruz'));
        $this->assertNull(SensitiveLogRedactor::name(''));
    }

    public function test_exception_context_omits_raw_message(): void
    {
        $exception = new RuntimeException('Private patient detail', 17);
        $context = SensitiveLogRedactor::exceptionContext($exception);

        $this->assertSame(RuntimeException::class, $context['exception_type']);
        $this->assertSame(17, $context['exception_code']);
        $this->assertArrayNotHasKey('message', $context);
        $this->assertStringNotContainsString('Private patient detail', json_encode($context));
    }
}
