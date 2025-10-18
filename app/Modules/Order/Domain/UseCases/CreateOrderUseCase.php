<?php

namespace App\Modules\Order\Domain\UseCases;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Core\Exceptions\ResourceNotFoundException;
use App\Core\Helpers\Params;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Order\Domain\Dtos\CreateOrderInputDto;
use App\Modules\Order\Domain\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Exceptions\TicketPerEventLimitExceededException;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;

class CreateOrderUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private OrderRepositoryInterface $orderRepository,
        private EventRepositoryInterface $eventRepository
    ) {}

    public function execute(CreateOrderInputDto $createOrderInputDto): OrderOutputDto
    {
        $authUser = $this->authenticator->getAuthUser();

        $event = $this->eventRepository->getById($createOrderInputDto->eventId);

        if ($event === null) {
            throw new ResourceNotFoundException(['event_id' => 'Evento não encontrado.']);
        }

        $countSoldTicketsByParticipant = $this->orderRepository->getCountSoldTicketsByParticipant($event->id, $authUser->id);
        $remainingTicketsPerParticipant = Params::maxTicketsPerEvent() - $countSoldTicketsByParticipant;

        if ($createOrderInputDto->quantity > $remainingTicketsPerParticipant) {
            throw new TicketPerEventLimitExceededException("A quantidade informada excede o seu limite de tickets para esse evento. Restam {$remainingTicketsPerParticipant} tickets disponíveis.");
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
            'pending'
        );

        $newOrder = $this->orderRepository->create($order);

        return OrderOutputDto::fromEntity($newOrder);
    }
}
