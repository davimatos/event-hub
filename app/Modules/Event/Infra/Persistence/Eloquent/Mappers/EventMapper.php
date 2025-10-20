<?php

namespace App\Modules\Event\Infra\Persistence\Eloquent\Mappers;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Infra\Persistence\Eloquent\Mappers\UserMapper;
use Illuminate\Database\Eloquent\Collection;

class EventMapper
{
    public static function toEntity(EventModel $eventModel): Event
    {
        return new Event(
            $eventModel->id,
            UserMapper::toEntity($eventModel->organizer),
            $eventModel->title,
            $eventModel->description,
            new Date($eventModel->date->format('Y-m-d')),
            new Money($eventModel->ticket_price),
            $eventModel->capacity,
            $eventModel->remaining_tickets,
            $eventModel->created_at,
            $eventModel->updated_at
        );
    }

    public static function toEntityCollection(Collection $eventModels): array
    {
        return $eventModels->map(fn (EventModel $eventModel) => self::toEntity($eventModel))->toArray();
    }

    public static function toPersistence(Event $event): array
    {
        return [
            'organizer_id' => $event->organizer->id,
            'title' => $event->title,
            'description' => $event->description,
            'date' => $event->date,
            'ticket_price' => $event->ticketPrice->value(),
            'capacity' => $event->capacity,
            'remaining_tickets' => $event->remainingTickets,
        ];
    }
}
