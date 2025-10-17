<?php

namespace App\Modules\Order\Infra\Repositories;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\ValueObjects\Date;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infra\Models\OrderModel;
use App\Modules\Ticket\Domain\Entities\Ticket;
use App\Modules\Ticket\Infra\Models\TicketModel;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\DB;

class OrderEloquentRepository implements OrderRepositoryInterface
{
    public function create(Order $order): Order
    {
        $orderModel = new OrderModel([
            'event_id' => $order->event->id,
            'participant_id' => $order->participant->id,
            'quantity' => $order->quantity,
            'ticket_price' => $order->ticketPrice->value(),
            'discount' => $order->discount->value(),
            'total_amount' => $order->totalAmount->value(),
            'status' => $order->status,
        ]);

        $ticketsToCreate = [];

        for ($c = 0; $c < $orderModel->quantity; $c++) {
            $ticketsToCreate[] = [
                'event_id' => $orderModel->event_id,
                'participant_id' => $orderModel->participant_id,
            ];
        }

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

        $event = new Event(
            id: $orderModel->event->id,
            organizer: new User(
                id: $orderModel->event->organizer->id,
                name: $orderModel->event->organizer->name,
                email: new Email($orderModel->event->organizer->email),
                type: UserType::from($orderModel->event->organizer->type),
                createdAt: $orderModel->event->organizer->created_at,
                updatedAt: $orderModel->event->organizer->updated_at
            ),
            title: $orderModel->event->title,
            description: $orderModel->event->description,
            date: new Date($orderModel->event->date->format('Y-m-d')),
            ticketPrice: new Money($orderModel->event->ticket_price),
            capacity: $orderModel->event->capacity,
            createdAt: $orderModel->event->created_at,
            updatedAt: $orderModel->event->updated_at
        );

        $participant = new User(
            id: $orderModel->participant->id,
            name: $orderModel->participant->name,
            email: new Email($orderModel->participant->email),
            type: UserType::from($orderModel->participant->type),
            createdAt: $orderModel->participant->created_at,
            updatedAt: $orderModel->participant->updated_at
        );

        $tickets = [];

        foreach ($orderModel->tickets as $ticket) {
            $tickets[] = new Ticket(
                id: $ticket->id,
                orderId: $ticket->order_id,
                eventId: $ticket->event_id,
                participantId: $ticket->participant_id,
                createdAt: $ticket->created_at,
                updatedAt: $ticket->updated_at
            );
        }

        return new Order(
            $orderModel->id,
            $event,
            $participant,
            $orderModel->quantity,
            new Money($orderModel->ticket_price),
            new Money($orderModel->discount),
            new Money($orderModel->total_amount),
            $orderModel->status,
            $tickets,
            $orderModel->created_at,
            $orderModel->updated_at
        );
    }
}
