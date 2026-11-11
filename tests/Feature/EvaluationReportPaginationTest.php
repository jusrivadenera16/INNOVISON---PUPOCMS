<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportsController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EvaluationReportPaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('eval_form', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->string('source_type')->nullable();
            $table->string('client_type')->nullable();
            $table->string('status');
            $table->timestamp('submitted_at');
            $table->integer('rating');
            $table->text('suggestions')->nullable();
            $table->text('sqd_answers')->nullable();
            foreach (['cc1', 'cc2', 'cc3'] as $field) $table->string($field)->nullable();
        });
        for ($day = 1; $day <= 6; $day++) {
            DB::table('eval_form')->insert([
                'status' => 'submitted', 'submitted_at' => sprintf('2026-11-%02d 14:00:00', $day), 'rating' => 5,
            ]);
        }
    }

    private function report(int $page): array
    {
        $request = Request::create('/admin/reports/evaluation-reports', 'GET', [
            'date_from' => '2026-11-01', 'date_to' => '2026-11-11', 'page' => $page,
        ]);
        $this->app->instance('request', $request);

        return app(ReportsController::class)->serviceEvaluationReport($request)->getData();
    }

    public function test_five_responses_per_page_with_filters_and_summary_preserved(): void
    {
        $data = $this->report(1);
        $items = $data['evaluationItems'];
        $this->assertSame(5, $items->perPage());
        $this->assertCount(5, $items);
        $this->assertSame(6, $data['totalSubmissions']);
        $this->assertSame([6, 5, 4, 3, 2], $items->pluck('id')->all());
        $html = $items->onEachSide(1)->links('admin.reports.partials.evaluation-pagination')->toHtml();
        $this->assertStringContainsString('Showing 1–5 of 6', $html);
        $this->assertStringContainsString('date_from=2026-11-01', $html);
        $this->assertStringContainsString('date_to=2026-11-11', $html);
        $this->assertStringContainsString('page=2#submitted-responses', $html);
        $this->assertStringContainsString('aria-current="page"', $html);

        $second = $this->report(2);
        $this->assertSame([1], $second['evaluationItems']->pluck('id')->all());
        $this->assertSame(6, $second['totalSubmissions']);
        $this->assertSame($data['averageRating'], $second['averageRating']);
        $html = $second['evaluationItems']->links('admin.reports.partials.evaluation-pagination')->toHtml();
        $this->assertStringContainsString('Showing 6–6 of 6', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringNotContainsString('rel="next"', $html);
    }

    public function test_pagination_is_hidden_when_only_one_page_exists(): void
    {
        DB::table('eval_form')->where('id', '>', 5)->delete();
        $items = $this->report(1)['evaluationItems'];
        $this->assertSame('', trim($items->links('admin.reports.partials.evaluation-pagination')->toHtml()));
    }
}
