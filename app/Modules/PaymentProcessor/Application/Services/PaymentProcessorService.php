<?php

namespace App\Modules\PaymentProcessor\Application\Services;

use App\Modules\Order\Application\Services\PaymentGatewayServiceInterface;
use App\Modules\Order\Domain\Entities\CreditCard;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\PaymentProcessor\Application\Services\Contract\PaymentProcessorServiceInterface;
use App\Modules\Shared\Domain\Adapters\LogAdapterInterface;

readonly class PaymentProcessorService implements PaymentProcessorServiceInterface
{
    const MAX_RETRIES = 3;

    public function __construct(
        private PaymentGatewayServiceInterface $paymentGatewayService,
        private LogAdapterInterface $logAdapter
    ) {}

    public function process(Order $order, CreditCard $creditCard): bool
    {
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                if ((bool) $this->paymentGatewayService->authorize($creditCard, $order->totalAmount) === true) {
                    return true;
                }
            } catch (\Exception $e) {
                $this->logAdapter->log(
                    'error',
                    "Falha na autorização de pagamento: {$e->getMessage()}",
                    'payment_processor',
                    ['attempt' => $attempt, 'order' => $order]
                );
            }
        }

        return false;
    }
}
