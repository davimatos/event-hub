<?php

namespace App\Modules\PaymentProcessor\Application\Services\Contract;

use App\Modules\Order\Domain\Entities\Order;

interface PaymentProcessorServiceInterface
{
    public function process(Order $order, int $retries): bool;
}
