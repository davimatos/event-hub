<?php

namespace App\Modules\Order\Domain\Dtos;

use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Ticket\Domain\Dtos\TicketOutputDto;

readonly class OrderOutputDto
{
    public function __construct(
        public ?string $id,
        public string $event_id,
        public string $participant_id,
        public int $quantity,
        public float $ticket_price,
        public float $discount,
        public float $total_amount,
        public string $status,
        public ?array $tickets = [],
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromEntity(Order $order): self
    {
        $ticketsOutputDto = [];

        foreach ($order->tickets as $ticket) {
            $ticketsOutputDto[] = TicketOutputDto::fromEntity($ticket);
        }

        return new self(
            id: $order->id,
            event_id: $order->event->id,
            participant_id: $order->participant->id,
            quantity: $order->quantity,
            ticket_price: $order->ticketPrice->value(),
            discount: $order->discount->value(),
            total_amount: $order->totalAmount->value(),
            status: $order->status->value,
            tickets: $ticketsOutputDto,
            created_at: $order->createdAt,
            updated_at: $order->updatedAt,
        );
    }
}
