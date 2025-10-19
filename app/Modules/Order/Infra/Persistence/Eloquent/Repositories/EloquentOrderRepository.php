<?php

namespace App\Modules\Order\Infra\Persistence\Eloquent\Repositories;

use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infra\Persistence\Eloquent\Mappers\OrderMapper;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\TicketModel;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(Order $order): Order
    {
        $orderModel = new OrderModel(OrderMapper::toPersistence($order));

        $orderModel->save();

        for ($c = 1; $c <= $orderModel->quantity; $c++) {
            TicketModel::create([
                'order_id' => $orderModel->id,
                'event_id' => $orderModel->event_id,
                'participant_id' => $orderModel->participant_id,
            ]);
        }

        return OrderMapper::toEntity($orderModel);
    }

    public function getCountSoldTicketsByParticipant(string $eventId, string $participantId): int
    {
        $soldTicketsCount = OrderModel::where('event_id', $eventId)
            ->where('participant_id', $participantId)
            ->where('status', '!=', OrderStatus::CANCELED->value)
            ->sum('quantity');

        return (int) $soldTicketsCount;
    }
}
