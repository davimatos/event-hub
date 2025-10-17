<?php

namespace App\Modules\Event\Domain\Repositories;

use App\Modules\Event\Domain\Entities\Event;

interface EventRepositoryInterface
{
    public function create(Event $event): Event;

    public function getById(string $id): ?Event;
}
