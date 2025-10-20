<?php

namespace App\Framework\Providers;

use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Infra\Persistence\Eloquent\Repositories\EloquentEventRepository;
use App\Modules\Order\Application\Services\NewOrderNotificationServiceInterface;
use App\Modules\Order\Application\Services\PaymentGatewayServiceInterface;
use App\Modules\Order\Domain\Repositories\DiscountCouponRepositoryInterface;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infra\Http\Services\FakePaymentGatewayService;
use App\Modules\Order\Infra\Http\Services\NewOrderNotificationService;
use App\Modules\Order\Infra\Persistence\Eloquent\Repositories\EloquentOrderRepository;
use App\Modules\Order\Infra\Persistence\Memory\Repositories\MemoryDiscountCouponRepository;
use App\Modules\PaymentProcessor\Application\Services\Contract\PaymentProcessorServiceInterface;
use App\Modules\PaymentProcessor\Application\Services\PaymentProcessorService;
use App\Modules\Shared\Application\Services\NotificationServiceInterface;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Adapters\LogAdapterInterface;
use App\Modules\Shared\Domain\Adapters\QueueAdapterInterface;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;
use App\Modules\Shared\Domain\Repositories\TransactionManagerInterface;
use App\Modules\Shared\Infra\Adapters\LaravelQueueAdapter;
use App\Modules\Shared\Infra\Adapters\MonologLogAdapter;
use App\Modules\Shared\Infra\Adapters\SanctrumAuthenticatorAdapter;
use App\Modules\Shared\Infra\Repositories\LaravelConfigParamsRepository;
use App\Modules\Shared\Infra\Repositories\Persistence\Eloquent\TransactionManager;
use App\Modules\Shared\Infra\Services\EmailNotificationService;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Infra\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConfigParamsRepositoryInterface::class, LaravelConfigParamsRepository::class);

        $this->app->bind(AuthenticatorAdapterInterface::class, SanctrumAuthenticatorAdapter::class);
        $this->app->bind(LogAdapterInterface::class, MonologLogAdapter::class);
        $this->app->bind(QueueAdapterInterface::class, LaravelQueueAdapter::class);

        $this->app->bind(TransactionManagerInterface::class, TransactionManager::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(DiscountCouponRepositoryInterface::class, MemoryDiscountCouponRepository::class);

        $this->app->bind(PaymentProcessorServiceInterface::class, PaymentProcessorService::class);
        $this->app->bind(PaymentGatewayServiceInterface::class, FakePaymentGatewayService::class);
        $this->app->bind(NotificationServiceInterface::class, EmailNotificationService::class);
        $this->app->bind(NewOrderNotificationServiceInterface::class, NewOrderNotificationService::class);
    }

    public function boot(): void {}
}
