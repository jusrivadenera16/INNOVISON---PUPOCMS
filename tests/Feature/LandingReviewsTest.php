<?php

namespace Tests\Feature;

use App\Http\Controllers\AppointmentController;
use App\Services\ClinicWorkflowService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LandingReviewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('eval_form', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->string('status');
            $table->string('client_type')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->integer('rating')->nullable();
            $table->text('suggestions')->nullable();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('email')->nullable();
        });

        $workflow = $this->mock(ClinicWorkflowService::class);
        $workflow->shouldReceive('studentAssistantWorkspaceAvailable')->andReturn(true);
        $workflow->shouldReceive('studentAssistantHoursLabel')->andReturn('8 AM - 5 PM');
        $workflow->shouldReceive('doctorIsAvailable')->andReturn(false);
        $workflow->shouldReceive('clinicHoursStatus')->andReturn(['is_open' => true]);
        $workflow->shouldReceive('activeClosure')->andReturn(null);
    }

    public function test_submitted_consented_reviews_are_public_even_without_suggestions(): void
    {
        $this->createReview(['suggestions' => 'Eligible review']);
        $this->createReview(['status' => 'requested', 'suggestions' => 'Not submitted']);
        $this->createReview(['submitted_at' => null, 'suggestions' => 'Missing submission']);
        $this->createReview(['consent_at' => null, 'suggestions' => 'Missing consent']);
        $this->createReview(['suggestions' => null]);
        $this->createReview(['suggestions' => '   ']);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('landingReviews', function ($reviews): bool {
                return $reviews->count() === 3
                    && $reviews->pluck('message')->filter()->values()->all() === ['Eligible review'];
            })
            ->assertSee('Eligible review')
            ->assertDontSee('Missing consent')
            ->assertDontSee('Not submitted');
    }

    public function test_rating_only_reviews_show_on_landing_and_home_without_empty_comment_markup(): void
    {
        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Maya', 'last_name' => 'Reviewer', 'user_role' => 'student',
        ]);
        $this->createReview(['user_id' => $userId, 'suggestions' => null, 'rating' => 5]);
        $this->createReview(['user_id' => $userId, 'suggestions' => '   ', 'rating' => 4]);
        $method = new \ReflectionMethod(AppointmentController::class, 'buildRecentEvaluationCollection');
        $homeReviews = $method->invoke(app(AppointmentController::class), 10);
        $this->assertCount(2, $homeReviews);
        $response = $this->get('/')->assertOk()
            ->assertViewHas('landingReviews', fn ($reviews): bool => $reviews->all() === $homeReviews->all())
            ->assertSee('Maya R.')->assertDontSee('Maya Reviewer');
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $section = '//*[@id="landingReviewsSection"]';
        $this->assertSame(2, $xpath->query($section . '//article[contains(@class, "feedback-slide")]')->length);
        $this->assertSame(2, $xpath->query($section . '//*[contains(@class, "feedback-stars")]')->length);
        $this->assertSame(0, $xpath->query($section . '//*[@class="feedback-quote"]')->length);
        $this->assertSame(0, $xpath->query($section . '//*[@class="feedback-copy"]/p')->length);
        $homeHtml = view('partials.clinic-review-cards', ['reviews' => $homeReviews])->render();
        $this->assertStringContainsString('5 out of 5 stars', $homeHtml);
        $this->assertStringContainsString('4 out of 5 stars', $homeHtml);
        $this->assertStringNotContainsString('<p>', $homeHtml);
    }

    public function test_it_displays_the_ten_most_recent_reviews_like_the_homepage(): void
    {
        for ($day = 1; $day <= 12; $day++) {
            $this->createReview([
                'suggestions' => 'Review ' . $day,
                'submitted_at' => sprintf('2026-10-%02d 12:00:00', $day),
            ]);
        }

        $this->get('/')
            ->assertOk()
            ->assertViewHas('landingReviews', fn ($reviews): bool => $reviews->pluck('message')->all() === [
                'Review 12', 'Review 11', 'Review 10', 'Review 9', 'Review 8',
                'Review 7', 'Review 6', 'Review 5', 'Review 4', 'Review 3',
            ]);
    }

    public function test_reviews_follow_announcements_and_precede_faqs_with_a_button_beside_login(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('No feedback has been shared yet.')
            ->assertSee('What PUPians Are Saying')
            ->assertDontSee('What Our Clients Say');
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/id="announcementModalOverlay".*id="landingReviewsSection".*id="landingFaqSection"/s',
            $html
        );

        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query(
            '//*[@id="landingReviewsButton" and @type="button" and @aria-controls="landingReviewsSection"]'
            . '/following-sibling::a[contains(@class, "portal-btn--idp")]'
        )->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsButton"]//svg/path[contains(@d, "M21 15")]')->length);
        $this->assertMatchesRegularExpression('/\.announcement-modal-overlay::before,\s*\.landing-reviews-heading p\s*\{/', $html);
    }

    public function test_review_text_is_escaped_and_only_valid_ratings_have_stars(): void
    {
        $this->createReview(['suggestions' => '<script>alert("review")</script>', 'rating' => 4]);
        $this->createReview(['suggestions' => 'Unrated feedback', 'rating' => 0]);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;review&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("review")</script>', false)
            ->assertSee('Clinic User');

        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsSection"]//*[@role="img" and @aria-label="4 out of 5 stars"]')->length);
        $this->assertSame(4, $xpath->query('//*[@id="landingReviewsSection"]//span[@class="is-filled"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="landingReviewsSection"]//*[@aria-label="0 out of 5 stars"]')->length);
    }

    public function test_the_homepage_and_landing_share_the_same_feed_with_masked_names(): void
    {
        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Maya',
            'last_name' => 'Reviewer',
            'user_role' => 'student',
            'email' => 'private-reviewer@example.test',
        ]);
        $this->createReview(['user_id' => $userId]);

        $method = new \ReflectionMethod(AppointmentController::class, 'buildRecentEvaluationCollection');
        $homeReviews = $method->invoke(app(AppointmentController::class), 10);

        $response = $this->get('/')
            ->assertOk()
            ->assertViewHas('landingReviews', fn ($reviews): bool => $reviews->all() === $homeReviews->all())
            ->assertSee('Maya R.')
            ->assertSee('Student')
            ->assertDontSee('Maya Reviewer')
            ->assertDontSee('private-reviewer@example.test');

        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsSection"]//article[contains(@class, "feedback-slide")]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsSection"]//*[@class="feedback-quote"]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsSection"]//svg[@class="avatar"]/use[@href="#landing-review-avatar"]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingReviewsSection"]//*[@class="comment-meta"]')->length);
    }

    public function test_mobile_navigation_has_both_toggle_icons_and_only_the_three_remaining_actions(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="landingNavToggle" and @aria-controls="landingNavMenu" and @aria-expanded="false" and @type="button"]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingNavToggle"]//svg[contains(@class, "landing-nav-toggle__menu")]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingNavToggle"]//svg[contains(@class, "landing-nav-toggle__close")]')->length);
        $this->assertSame(3, $xpath->query('//nav[@id="landingNavMenu"]/button')->length);
        $this->assertSame(0, $xpath->query('//*[@id="landingHomeButton"]')->length);
        foreach (['announcementBtn', 'landingFaqButton', 'bp-toggle-chat'] as $id) {
            $this->assertSame(1, $xpath->query('//nav[@id="landingNavMenu"]/button[@id="' . $id . '"]')->length);
        }
    }

    public function test_the_chestpiece_is_a_background_inside_faqs_not_a_separate_section(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/\.landing-footer-stethoscope\s*\{[^}]*inset:\s*-420px 0 0;[^}]*overflow:\s*hidden\s*;/s', $html);
        $this->assertMatchesRegularExpression(
            '/id="landingReviewsSection".*id="landingFaqSection".*class="landing-announcement-stethoscope landing-footer-stethoscope".*<\/main>.*<footer/s',
            $html
        );

        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="landingFaqSection"]/div[contains(@class, "landing-footer-stethoscope") and @aria-hidden="true"]//canvas')->length);
        $this->assertSame(0, $xpath->query('//main/div[contains(@class, "landing-footer-stethoscope")]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="landingFaqSection"]/div[@class="landing-faq-inner"]//*[@id="landingFaqTitle"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="announcementModalOverlay"]//*[contains(@class, "landing-stethoscope-chestpiece")]')->length);
        $this->assertSame(1, $xpath->query('//img[contains(@class, "landing-stethoscope-chestpiece-image")]')->length);
    }

    public function test_missing_evaluation_storage_keeps_the_landing_page_available(): void
    {
        Schema::drop('eval_form');

        $this->get('/')
            ->assertOk()
            ->assertViewHas('landingReviews', fn ($reviews): bool => $reviews->isEmpty())
            ->assertSee('No feedback has been shared yet.');
    }

    private function createReview(array $attributes = []): void
    {
        DB::table('eval_form')->insert(array_merge([
            'status' => 'submitted',
            'client_type' => 'Student',
            'submitted_at' => '2026-10-01 12:00:00',
            'consent_at' => '2026-10-01 12:00:00',
            'rating' => 5,
            'suggestions' => 'Helpful clinic service.',
        ], $attributes));
    }
}
