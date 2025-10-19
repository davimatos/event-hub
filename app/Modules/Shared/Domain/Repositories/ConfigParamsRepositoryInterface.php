<?php

namespace App\Modules\Shared\Domain\Repositories;

interface ConfigParamsRepositoryInterface
{
    public static function rateLimitPerMinute(): int;

    public static function authTokenLifetimeInMinutes(): int;

    public static function maxTicketsPerOrder(): int;

    public static function maxTicketsPerEvent(): int;
}
