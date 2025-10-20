<?php

namespace App\Modules\Event\Domain\Repositories;

use App\Modules\Event\Domain\Entities\Event;

interface EventRepositoryInterface
{
    public function create(Event $event): Event;

    public function getById(string $id): ?Event;

    public function getAll(): array;

    public function getRemainingTickets(string $eventId): int;

    public function decrementRemainingTickets(string $eventId, int $quantity): void;
}
