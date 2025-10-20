<?php

namespace App\Modules\Event\Infra\Persistence\Eloquent\Repositories;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Infra\Persistence\Eloquent\Mappers\EventMapper;
use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use Illuminate\Support\Facades\DB;

class EloquentEventRepository implements EventRepositoryInterface
{
    public function create(Event $event): Event
    {
        $eventModel = new EventModel(EventMapper::toPersistence($event));

        $eventModel->save();

        return EventMapper::toEntity($eventModel);
    }

    public function getById(string $id): ?Event
    {
        $eventModel = EventModel::find($id);

        if ($eventModel === null) {
            return null;
        }

        return EventMapper::toEntity($eventModel);
    }

    public function getRemainingTickets(string $eventId): int
    {
        return DB::table('events')
            ->where('id', $eventId)
            ->lockForUpdate()
            ->value('remaining_tickets');
    }

    public function decrementRemainingTickets(string $eventId, int $quantity): void
    {
        EventModel::where('id', $eventId)
            ->decrement('remaining_tickets', $quantity);
    }
}
