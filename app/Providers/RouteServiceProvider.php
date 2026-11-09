<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $emailKey = hash('sha256', mb_strtolower(trim((string) $request->input('email'))));
            $ipKey = $request->ip() ?: 'unknown';

            return [
                Limit::perMinute(5)->by('login-account-ip:' . hash('sha256', $emailKey . '|' . $ipKey)),
                Limit::perMinute(30)->by('login-ip:' . $ipKey),
            ];
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
