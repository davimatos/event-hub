<?php

namespace App\Modules\Ticket\Infra\Persistence\Eloquent\Mappers;

use App\Modules\Ticket\Domain\Entities\Ticket;
use App\Modules\Ticket\Infra\Persistence\Eloquent\Models\TicketModel;

class TicketMapper
{
    public static function toEntity(TicketModel $ticketModel): Ticket
    {
        return new Ticket(
            $ticketModel->id,
            $ticketModel->order_id,
            $ticketModel->event_id,
            $ticketModel->participant_id,
            $ticketModel->created_at,
            $ticketModel->updated_at
        );
    }

    public static function toPersistence(Ticket $ticket): array
    {
        return [
            'order_id' => $ticket->orderId,
            'event_id' => $ticket->eventId,
            'participant_id' => $ticket->participantId,
            'used_at' => $ticket->usedAt,
        ];
    }
}
