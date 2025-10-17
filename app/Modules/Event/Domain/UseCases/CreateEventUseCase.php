<?php

namespace App\Modules\Event\Domain\UseCases;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Core\Exceptions\UnauthorizedException;
use App\Modules\Event\Domain\Dtos\CreateEventInputDto;
use App\Modules\Event\Domain\Dtos\EventOutputDto;
use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Domain\ValueObjects\Date;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Dtos\UserOutputDto;

class CreateEventUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private EventRepositoryInterface $eventRepository
    ) {}

    public function execute(CreateEventInputDto $createEventInputDto): EventOutputDto
    {

        $authUser = $this->authenticator->getAuthUser();

        if ($authUser->isOrganizer() === false) {
            throw new UnauthorizedException;
        }

        $event = new Event(
            id: null,
            organizer: $authUser,
            title: $createEventInputDto->title,
            description: $createEventInputDto->description,
            date: new Date($createEventInputDto->date),
            ticketPrice: new Money($createEventInputDto->ticketPrice),
            capacity: $createEventInputDto->capacity,
        );

        $newEvent = $this->eventRepository->create($event);

        return EventOutputDto::fromEntity($newEvent);
    }
}
