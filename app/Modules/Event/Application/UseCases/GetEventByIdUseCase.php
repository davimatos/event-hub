<?php

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Application\Dtos\EventOutputDto;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Shared\Application\Exceptions\ResourceNotFoundException;

readonly class GetEventByIdUseCase
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    public function execute(string $id): EventOutputDto
    {
        $event = $this->eventRepository->getById($id);

        if ($event === null) {
            throw new ResourceNotFoundException;
        }

        return EventOutputDto::fromEntity($event);
    }
}
