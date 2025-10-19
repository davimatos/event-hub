<?php

namespace App\Modules\Order\Application\UseCases;

use App\Modules\Event\Application\Exceptions\EventCapacityExceededException;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Order\Application\Exceptions\TicketsPerEventLimitExceededException;
use App\Modules\Order\Application\Exceptions\TicketsPerOrderLimitExceededException;
use App\Modules\Order\Domain\Dtos\CreateOrderInputDto;
use App\Modules\Order\Domain\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Shared\Application\Exceptions\ResourceNotFoundException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;

readonly class CreateOrderUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private ConfigParamsRepositoryInterface $configParams,
        private OrderRepositoryInterface $orderRepository,
        private EventRepositoryInterface $eventRepository
    ) {}

    public function execute(CreateOrderInputDto $createOrderInputDto): OrderOutputDto
    {
        $authUser = $this->authenticator->getAuthUser();

        if ($createOrderInputDto->quantity > $this->configParams->maxTicketsPerOrder()) {
            throw new TicketsPerOrderLimitExceededException;
        }

        $event = $this->eventRepository->getById($createOrderInputDto->eventId);

        if ($event === null) {
            throw new ResourceNotFoundException(['event_id' => 'Evento não encontrado.']);
        }

        if ($event->hasSoldOut() === true) {
            throw new EventCapacityExceededException;
        }

        if ($event->hasAvailableTickets($createOrderInputDto->quantity) === false) {
            throw new TicketsPerEventLimitExceededException("A quantidade informada excede a quantidade de tickets disponíveis para o evento. Restam {$event->remainingTickets} tickets disponíveis.");
        }

        $countSoldTicketsByParticipant = $this->orderRepository->getCountSoldTicketsByParticipant($event->id, $authUser->id);
        $remainingTicketsPerParticipant = $this->configParams->maxTicketsPerEvent() - $countSoldTicketsByParticipant;

        if ($createOrderInputDto->quantity > $remainingTicketsPerParticipant) {
            throw new TicketsPerEventLimitExceededException("A quantidade informada excede o seu limite de tickets para esse evento. Restam {$remainingTicketsPerParticipant} tickets disponíveis.");
        }

        $orderDiscount = new Money(0);
        $totalOrderAmount = new Money(($createOrderInputDto->quantity * $event->ticketPrice->value()) - $orderDiscount->value());

        $order = new Order(
            null,
            $event,
            $authUser,
            $createOrderInputDto->quantity,
            new Money($event->ticketPrice->value()),
            $orderDiscount,
            $totalOrderAmount,
            OrderStatus::PENDING
        );

        $newOrder = $this->orderRepository->create($order);

        return OrderOutputDto::fromEntity($newOrder);
    }
}
