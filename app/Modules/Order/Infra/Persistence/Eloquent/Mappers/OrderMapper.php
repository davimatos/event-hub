<?php

namespace App\Modules\Order\Infra\Persistence\Eloquent\Mappers;

use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Event\Infra\Persistence\Eloquent\Mappers\EventMapper;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\Ticket\Infra\Persistence\Eloquent\Mappers\TicketMapper;
use App\Modules\User\Infra\Persistence\Eloquent\Mappers\UserMapper;

class OrderMapper
{
    public static function toEntity(OrderModel $orderModel): Order
    {
        $tickets = [];

        foreach ($orderModel->tickets as $ticket) {
            $tickets[] = TicketMapper::toEntity($ticket);
        }

        return new Order(
            $orderModel->id,
            EventMapper::toEntity($orderModel->event),
            UserMapper::toEntity($orderModel->participant),
            $orderModel->quantity,
            new Money($orderModel->ticket_price),
            new Money($orderModel->discount),
            new Money($orderModel->total_amount),
            OrderStatus::from($orderModel->status),
            $tickets,
            $orderModel->created_at,
            $orderModel->updated_at
        );
    }

    public static function toPersistence(Order $order): array
    {
        return [
            'event_id' => $order->event->id,
            'participant_id' => $order->participant->id,
            'quantity' => $order->quantity,
            'ticket_price' => $order->ticketPrice->value(),
            'discount' => $order->discount->value(),
            'total_amount' => $order->totalAmount->value(),
            'status' => $order->status->value,
        ];
    }
}
