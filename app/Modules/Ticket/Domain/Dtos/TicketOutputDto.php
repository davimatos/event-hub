<?php

namespace App\Modules\Ticket\Domain\Dtos;

use App\Modules\Ticket\Domain\Entities\Ticket;

readonly class TicketOutputDto
{
    public function __construct(
        public ?string $id,
        public string $order_id,
        public string $event_id,
        public string $participant_id,
        public ?string $used_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromEntity(Ticket $ticket): self
    {
        return new self(
            id: $ticket->id,
            order_id: $ticket->orderId,
            event_id: $ticket->eventId,
            participant_id: $ticket->participantId,
            used_at: $ticket->usedAt,
            created_at: $ticket->createdAt,
            updated_at: $ticket->updatedAt,
        );
    }
}
