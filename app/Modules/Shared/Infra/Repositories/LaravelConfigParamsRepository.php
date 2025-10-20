<?php

namespace App\Modules\Shared\Infra\Repositories;

use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;

class LaravelConfigParamsRepository implements ConfigParamsRepositoryInterface
{
    public function rateLimitPerMinute(): int
    {
        return config('params.rate_limit_per_minute');
    }

    public function authTokenLifetimeInMinutes(): int
    {
        return config('params.auth_token_lifetime_minutes');
    }

    public function maxTicketsPerOrder(): int
    {
        return config('params.max_tickets_per_order');
    }

    public function maxTicketsPerEvent(): int
    {
        return config('params.max_tickets_per_event');
    }
}
