<?php

namespace App\Modules\Shared\Domain\Repositories;

interface ConfigParamsRepositoryInterface
{
    public function rateLimitPerMinute(): int;

    public function authTokenLifetimeInMinutes(): int;

    public function maxTicketsPerOrder(): int;

    public function maxTicketsPerEvent(): int;
}
