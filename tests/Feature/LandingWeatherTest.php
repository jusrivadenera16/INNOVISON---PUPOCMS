<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LandingWeatherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    protected function tearDown(): void
    {
        Cache::forget('landing_weather.open_meteo.v2');

        parent::tearDown();
    }

    public function test_it_redirects_the_removed_weather_preview_query_to_the_clean_landing_url(): void
    {
        $response = $this->get('/?weather-preview=all');

        $response->assertRedirect('http://localhost');
    }

    public function test_it_returns_normalized_open_meteo_weather_for_the_landing_page(): void
    {
        Cache::forget('landing_weather.open_meteo.v2');
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'time' => '2026-09-11T15:30',
                    'temperature_2m' => 30.6,
                    'relative_humidity_2m' => 72,
                    'apparent_temperature' => 35.1,
                    'precipitation' => 0.1,
                    'rain' => 0.1,
                    'showers' => 0.0,
                    'weather_code' => 2,
                    'cloud_cover' => 49,
                    'is_day' => 1,
                    'wind_speed_10m' => 12.7,
                ],
                'hourly' => [
                    'time' => ['2026-09-11T14:00', '2026-09-11T15:00', '2026-09-11T16:00'],
                    'uv_index' => [8.1, 7.2, 4.8],
                    'precipitation_probability' => [28, 41, 35],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/landing/weather');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'temperature' => 30.6,
                    'apparent_temperature' => 35.1,
                    'precipitation' => 0.1,
                    'rain' => 0.1,
                    'showers' => 0.0,
                    'precipitation_probability' => 41,
                    'cloud_cover' => 49,
                    'humidity' => 72,
                    'wind_speed' => 12.7,
                    'weather_code' => 2,
                    'is_day' => 1,
                    'uv_index' => 7.2,
                    'observed_at' => '2026-09-11T15:30',
                    'timezone' => 'Asia/Manila',
                ],
                'stale' => false,
                'source' => 'Open-Meteo',
            ]);

        Http::assertSent(function ($request) {
            return strpos($request->url(), 'https://api.open-meteo.com/v1/forecast?') === 0
                && $request['timezone'] === 'Asia/Manila'
                && $request['hourly'] === 'uv_index,precipitation_probability'
                && strpos($request['current'], 'is_day') !== false
                && strpos($request['current'], 'precipitation') !== false
                && strpos($request['current'], 'cloud_cover') !== false
                && (int) $request['forecast_days'] === 1;
        });
    }

    public function test_it_returns_the_last_successful_weather_when_open_meteo_is_unavailable(): void
    {
        Cache::put('landing_weather.open_meteo.v2', [
            'cached_at' => now()->subMinutes(30),
            'data' => [
                'temperature' => 29.4,
                'apparent_temperature' => 33.2,
                'precipitation' => 0.0,
                'rain' => 0.0,
                'showers' => 0.0,
                'precipitation_probability' => 20,
                'cloud_cover' => 76,
                'humidity' => 70,
                'wind_speed' => 8.5,
                'weather_code' => 3,
                'uv_index' => 5.1,
                'observed_at' => '2026-09-11T15:00',
                'timezone' => 'Asia/Manila',
            ],
        ], now()->addHours(24));

        Http::fake([
            'api.open-meteo.com/*' => Http::response([], 503),
        ]);

        $response = $this->getJson('/landing/weather');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'temperature' => 29.4,
                    'observed_at' => '2026-09-11T15:00',
                ],
                'stale' => true,
                'source' => 'Open-Meteo',
            ]);
    }
}
