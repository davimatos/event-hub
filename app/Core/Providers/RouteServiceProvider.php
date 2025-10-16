<?php

namespace App\Core\Providers;

use App\Core\Helpers\Params;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(Params::rateLimitPerMinute())->by($request->user()?->id ?: $request->ip());
        });
    }
}
