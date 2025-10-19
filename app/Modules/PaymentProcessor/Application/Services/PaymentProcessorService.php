<?php

namespace App\Modules\PaymentProcessor\Application\Services;

use App\Modules\Order\Application\Services\PaymentGatewayServiceInterface;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\PaymentProcessor\Application\Services\Contract\PaymentProcessorServiceInterface;
use Illuminate\Support\Facades\Log;

readonly class PaymentProcessorService implements PaymentProcessorServiceInterface
{
    const MAX_RETRIES = 3;

    const RETRY_DELAY_SECONDS = 2;

    public function __construct(
        private PaymentGatewayServiceInterface $paymentGatewayService,
    ) {}

    public function process(Order $order, int $retries = self::MAX_RETRIES): bool
    {
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                if ((bool) $this->paymentGatewayService->authorize($order->totalAmount) === true) {
                    return true;
                }
            } catch (\Exception $e) {
                Log::debug('Payment authorization attempt '.$attempt.' failed: '.$e->getMessage());
            }

            sleep(self::RETRY_DELAY_SECONDS);
        }

        return false;
    }
}
