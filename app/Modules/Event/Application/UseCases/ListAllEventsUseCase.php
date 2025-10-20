<?php

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Domain\Dtos\EventOutputDto;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Shared\Domain\Dtos\CollectionOutputDto;

readonly class ListAllEventsUseCase
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    public function execute(): CollectionOutputDto
    {
        $events = $this->eventRepository->getAll();

        return CollectionOutputDto::fromEntities($events, EventOutputDto::class);
    }
}
