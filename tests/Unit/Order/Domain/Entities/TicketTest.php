<?php

namespace Tests\Unit\Order\Domain\Entities;

use App\Modules\Order\Domain\Entities\Ticket;
use Tests\TestCase;

class TicketTest extends TestCase
{
    public function test_create_ticket_successfully()
    {
        $ticket = new Ticket(
            id: 'ticket_123',
            orderId: 'order_456',
            eventId: 'event_789',
            participantId: 'participant_101',
            usedAt: null,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals('ticket_123', $ticket->id);
        $this->assertEquals('order_456', $ticket->orderId);
        $this->assertEquals('event_789', $ticket->eventId);
        $this->assertEquals('participant_101', $ticket->participantId);
        $this->assertNull($ticket->usedAt);
        $this->assertEquals('2025-01-01 10:00:00', $ticket->createdAt);
        $this->assertEquals('2025-01-01 10:00:00', $ticket->updatedAt);
    }
}

