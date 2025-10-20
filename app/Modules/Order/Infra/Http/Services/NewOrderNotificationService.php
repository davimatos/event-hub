<?php

namespace App\Modules\Order\Infra\Http\Services;

use App\Modules\Order\Application\Services\NewOrderNotificationServiceInterface;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Shared\Domain\Adapters\QueueAdapterInterface;
use App\Modules\Shared\Infra\Jobs\SendNotificationJob;

readonly class NewOrderNotificationService implements NewOrderNotificationServiceInterface
{
    public function __construct(
        private QueueAdapterInterface $queueAdapter,
    ) {}

    public function execute(Order $order): void
    {
        $this->sendMessage(
            $order->event->organizer->email,
            "Novo pedido #{$order->id} criado para o evento \"{$order->event->title}\". {$order->quantity} ticket(s) vendidos. Total: R$ ".number_format($order->totalAmount->value() / 100, 2, ',', '.')
        );

        $this->sendMessage(
            $order->participant->email,
            "Seu pedido #{$order->id} para o evento \"{$order->event->title}\" foi criado com sucesso. Você comprou {$order->quantity} ticket(s). Total: R$ ".number_format($order->totalAmount->value() / 100, 2, ',', '.')
        );
    }

    private function sendMessage(string $recipient, string $message): void
    {
        $this->queueAdapter->dispatch(
            SendNotificationJob::class,
            [
                'recipient' => $recipient,
                'message' => $message,
            ],
            'notifications'
        );
    }
}
