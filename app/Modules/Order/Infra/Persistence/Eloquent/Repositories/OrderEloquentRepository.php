<?php

namespace App\Modules\Order\Infra\Persistence\Eloquent\Repositories;

use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infra\Persistence\Eloquent\Mappers\OrderMapper;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\Ticket\Infra\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Support\Facades\DB;

class OrderEloquentRepository implements OrderRepositoryInterface
{
    public function create(Order $order): Order
    {
        $orderModel = new OrderModel(OrderMapper::toPersistence($order));

        DB::transaction(function () use ($orderModel) {

            $orderModel->save();

            for ($c = 0; $c < $orderModel->quantity; $c++) {
                TicketModel::create([
                    'order_id' => $orderModel->id,
                    'event_id' => $orderModel->event_id,
                    'participant_id' => $orderModel->participant_id,
                ]);
            }

        }, attempts: 3);

        return OrderMapper::toEntity($orderModel);
    }

    public function getCountSoldTicketsByParticipant(string $eventId, string $participantId): int
    {
        $soldTicketsCount = OrderModel::where('event_id', $eventId)
                                        ->where('participant_id', $participantId)
                                        ->where('status', '!=', 'canceled')
                                        ->sum('quantity');

        return (int) $soldTicketsCount;
    }
}
