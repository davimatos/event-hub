<?php

namespace App\Modules\Event\Infra\Repositories;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Domain\ValueObjects\Date;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Event\Infra\Models\EventModel;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;

class EventEloquentRepository implements EventRepositoryInterface
{
    public function create(Event $event): Event
    {
        $eventModel = new EventModel([
            'organizer_id' => $event->organizer->id,
            'title' => $event->title,
            'description' => $event->description,
            'date' => $event->date,
            'ticket_price' => $event->ticketPrice->value(),
            'capacity' => $event->capacity,
        ]);

        $eventModel->save();

        return new Event(
            $eventModel->id,
            new User(
                id: $eventModel->organizer->id,
                name: $eventModel->organizer->name,
                email: new Email($eventModel->organizer->email),
                type: UserType::from($eventModel->organizer->type),
                createdAt: $eventModel->organizer->created_at,
                updatedAt: $eventModel->organizer->updated_at
            ),
            $eventModel->title,
            $eventModel->description,
            new Date($eventModel->date->format('Y-m-d')),
            new Money($eventModel->ticket_price),
            $eventModel->capacity,
            $eventModel->created_at,
            $eventModel->updated_at
        );
    }
}
