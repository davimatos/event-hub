<?php

namespace App\Modules\Order\Application\Services;

use App\Modules\Event\Domain\ValueObjects\Money;

interface PaymentGatewayServiceInterface
{
    public function authorize(Money $amount): string;
}
