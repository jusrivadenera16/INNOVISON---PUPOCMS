<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LandingWeatherController extends Controller
{
    private const CACHE_KEY = 'landing_weather.open_meteo.v2';
    private const CACHE_MINUTES = 10;
    private const LAST_SUCCESS_HOURS = 24;

    public function __invoke(): JsonResponse
    {
        $cachedWeather = Cache::get(self::CACHE_KEY);

        if ($this->isFresh($cachedWeather)) {
            return response()->json($this->responsePayload($cachedWeather, false));
        }

        try {
            $response = Http::acceptJson()
                ->timeout(6)
                ->retry(2, 200)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => 14.5176,
                    'longitude' => 121.0509,
                    'current' => implode(',', [
                        'temperature_2m',
                        'relative_humidity_2m',
                        'apparent_temperature',
                        'precipitation',
                        'rain',
                        'showers',
                        'weather_code',
                        'cloud_cover',
                        'is_day',
                        'wind_speed_10m',
                    ]),
                    'hourly' => 'uv_index,precipitation_probability',
                    'forecast_days' => 1,
                    'timezone' => 'Asia/Manila',
                ]);

            $response->throw();
            $weather = $this->normalizeWeather($response->json());

            Cache::put(self::CACHE_KEY, $weather, now()->addHours(self::LAST_SUCCESS_HOURS));

            return response()->json($this->responsePayload($weather, false));
        } catch (Throwable $exception) {
            Log::warning('Unable to refresh landing weather from Open-Meteo.', [
                'exception' => $exception instanceof RequestException
                    ? $exception->getMessage()
                    : get_class($exception),
            ]);

            if (is_array($cachedWeather) && isset($cachedWeather['data'])) {
                return response()->json($this->responsePayload($cachedWeather, true));
            }

            return response()->json([
                'message' => 'Live weather is temporarily unavailable.',
            ], 503);
        }
    }

    private function isFresh($weather): bool
    {
        if (! is_array($weather) || empty($weather['cached_at']) || ! isset($weather['data'])) {
            return false;
        }

        return now()->diffInMinutes($weather['cached_at']) < self::CACHE_MINUTES;
    }

    private function normalizeWeather(array $payload): array
    {
        $current = $payload['current'] ?? [];
        $required = [
            'temperature_2m',
            'relative_humidity_2m',
            'apparent_temperature',
            'precipitation',
            'rain',
            'showers',
            'weather_code',
            'cloud_cover',
            'is_day',
            'wind_speed_10m',
            'time',
        ];

        foreach ($required as $field) {
            if (! array_key_exists($field, $current)) {
                throw new \UnexpectedValueException("Open-Meteo response is missing {$field}.");
            }
        }

        $observedAt = (string) $current['time'];
        $uvIndex = $this->hourlyValueForCurrentHour($payload, 'uv_index', $observedAt);
        $precipitationProbability = $this->hourlyValueForCurrentHour($payload, 'precipitation_probability', $observedAt);

        return [
            'cached_at' => now(),
            'data' => [
                'temperature' => (float) $current['temperature_2m'],
                'apparent_temperature' => (float) $current['apparent_temperature'],
                'precipitation' => (float) $current['precipitation'],
                'rain' => (float) $current['rain'],
                'showers' => (float) $current['showers'],
                'precipitation_probability' => (int) round($precipitationProbability),
                'cloud_cover' => (int) round($current['cloud_cover']),
                'humidity' => (int) round($current['relative_humidity_2m']),
                'wind_speed' => (float) $current['wind_speed_10m'],
                'weather_code' => (int) $current['weather_code'],
                'is_day' => (int) $current['is_day'],
                'uv_index' => (float) $uvIndex,
                'observed_at' => $observedAt,
                'timezone' => 'Asia/Manila',
            ],
        ];
    }

    private function hourlyValueForCurrentHour(array $payload, string $field, string $observedAt): float
    {
        $times = $payload['hourly']['time'] ?? [];
        $values = $payload['hourly'][$field] ?? [];
        $currentHour = substr($observedAt, 0, 13);

        foreach ($times as $index => $time) {
            if (substr((string) $time, 0, 13) === $currentHour && isset($values[$index])) {
                return (float) $values[$index];
            }
        }

        throw new \UnexpectedValueException("Open-Meteo response is missing the current hourly {$field} value.");
    }

    private function responsePayload(array $weather, bool $stale): array
    {
        return [
            'data' => $weather['data'],
            'stale' => $stale,
            'source' => 'Open-Meteo',
        ];
    }
}
