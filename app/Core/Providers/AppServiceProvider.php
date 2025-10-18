<?php

namespace App\Core\Providers;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Core\Adapters\Auth\SanctrumAuthenticatorAdapter;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Infra\Persistence\Eloquent\Repositories\EventEloquentRepository;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infra\Persistence\Eloquent\Repositories\OrderEloquentRepository;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Infra\Persistence\Eloquent\Repositories\UserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticatorAdapterInterface::class, SanctrumAuthenticatorAdapter::class);

        $this->app->bind(UserRepositoryInterface::class, UserEloquentRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventEloquentRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderEloquentRepository::class);
    }

    public function boot(): void {}
}
