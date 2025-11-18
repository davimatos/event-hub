<?php

namespace App\Modules\Event\Application\Dtos;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\User\Application\Dtos\UserOutputDto;

readonly class EventOutputDto
{
    public function __construct(
        public string $id,
        public UserOutputDto $organizer,
        public string $title,
        public string $description,
        public string $date,
        public float $ticket_price,
        public int $capacity,
        public int $remaining_tickets,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromEntity(Event $event): self
    {
        return new self(
            id: $event->id,
            organizer: UserOutputDto::fromEntity($event->organizer),
            title: $event->title,
            description: $event->description,
            date: $event->date,
            ticket_price: $event->ticketPrice->value(),
            capacity: $event->capacity,
            remaining_tickets: $event->remainingTickets,
            created_at: $event->createdAt,
            updated_at: $event->updatedAt,
        );
    }
}
