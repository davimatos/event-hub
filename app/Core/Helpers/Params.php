<?php

namespace App\Core\Helpers;

class Params
{
    public static function rateLimitPerMinute(): int
    {
        return config('params.rate_limit_per_minute');
    }

    public static function authTokenLifetimeInMinutes(): int
    {
        return config('params.auth_token_lifetime_minutes');
    }
}
