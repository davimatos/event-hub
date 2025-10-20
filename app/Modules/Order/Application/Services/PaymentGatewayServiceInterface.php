<?php

namespace App\Modules\Order\Application\Services;

use App\Modules\Order\Domain\Entities\CreditCard;
use App\Modules\Shared\Domain\ValueObjects\Money;

interface PaymentGatewayServiceInterface
{
    public function authorize(CreditCard $creditCard, Money $amount): string;
}
