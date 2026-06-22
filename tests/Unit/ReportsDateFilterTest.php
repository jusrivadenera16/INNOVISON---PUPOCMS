<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportsController;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ReportsDateFilterTest extends TestCase
{
    public function test_it_parses_the_display_date_format(): void
    {
        $this->assertSame('2026-06-19', $this->parseDate('19/06/2026')->toDateString());
    }

    public function test_it_accepts_legacy_iso_dates_and_falls_back_for_invalid_values(): void
    {
        $fallback = Carbon::parse('2026-06-01');

        $this->assertSame('2026-06-20', $this->parseDate('2026-06-20', $fallback)->toDateString());
        $this->assertSame('2026-06-01', $this->parseDate('not-a-date', $fallback)->toDateString());
    }

    private function parseDate(string $value, ?Carbon $fallback = null): Carbon
    {
        $controller = new ReportsController();
        $method = new ReflectionMethod($controller, 'parseReportDate');
        $method->setAccessible(true);

        return $method->invoke($controller, $value, $fallback ?? Carbon::parse('2026-06-01'));
    }
}
