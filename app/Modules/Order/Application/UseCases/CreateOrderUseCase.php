<?php

namespace App\Modules\Order\Application\UseCases;

use App\Modules\Event\Application\Exceptions\EventCapacityExceededException;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Order\Application\Exceptions\OrderPaymentFailException;
use App\Modules\Order\Application\Exceptions\TicketsPerEventLimitExceededException;
use App\Modules\Order\Application\Exceptions\TicketsPerOrderLimitExceededException;
use App\Modules\Order\Application\Services\NewOrderNotificationServiceInterface;
use App\Modules\Order\Domain\Dtos\CreateOrderInputDto;
use App\Modules\Order\Domain\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Entities\CreditCard;
use App\Modules\Order\Domain\Entities\DiscountCoupon;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Order\Domain\Repositories\DiscountCouponRepositoryInterface;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\PaymentProcessor\Application\Services\Contract\PaymentProcessorServiceInterface;
use App\Modules\Shared\Application\Exceptions\ResourceNotFoundException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;
use App\Modules\Shared\Domain\Repositories\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Money;

readonly class CreateOrderUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private ConfigParamsRepositoryInterface $configParams,
        private OrderRepositoryInterface $orderRepository,
        private EventRepositoryInterface $eventRepository,
        private TransactionManagerInterface $transactionManager,
        private DiscountCouponRepositoryInterface $discountCouponRepository,
        private PaymentProcessorServiceInterface $paymentProcessor,
        private NewOrderNotificationServiceInterface $newOrderNotification,
    ) {}

    public function execute(CreateOrderInputDto $createOrderInputDto): OrderOutputDto
    {
        $authUser = $this->authenticator->getAuthUser();

        $this->validateTicketsPerOrderLimit($createOrderInputDto->quantity);

        $event = $this->validateEventExists($createOrderInputDto->eventId);

        $this->validateEventAvailability($event, $createOrderInputDto->quantity);

        $this->validateParticipantTicketLimit($event->id, $authUser->id, $createOrderInputDto->quantity);

        $orderDiscount = $this->calculateDiscount($createOrderInputDto, $event);
        $totalOrderAmount = $this->calculateTotalAmount($createOrderInputDto->quantity, $event, $orderDiscount);

        $order = new Order(
            null,
            $event,
            $authUser,
            $createOrderInputDto->quantity,
            new Money($event->ticketPrice->value()),
            $orderDiscount,
            $totalOrderAmount,
            OrderStatus::CONFIRMED
        );

        $creditCard = new CreditCard(
            $createOrderInputDto->cardNumber,
            $createOrderInputDto->cardHolderName,
            $createOrderInputDto->cardExpirationDate,
            $createOrderInputDto->cardCvv,
        );

        $newOrder = null;

        $this->transactionManager->run(function () use ($order, $creditCard, &$newOrder) {

            $this->eventRepository->getRemainingTickets($order->event->id);

            $isPaymentAuthorized = $this->paymentProcessor->process($order, $creditCard);

            if ($isPaymentAuthorized === false) {
                throw new OrderPaymentFailException;
            }

            $newOrder = $this->orderRepository->create($order);

            $this->eventRepository->decrementRemainingTickets($order->event->id, $order->quantity);
        });

        $this->newOrderNotification->execute($newOrder);

        return OrderOutputDto::fromEntity($newOrder);
    }

    private function validateTicketsPerOrderLimit(int $quantity): void
    {
        if ($quantity > $this->configParams->maxTicketsPerOrder()) {
            throw new TicketsPerOrderLimitExceededException;
        }
    }

    private function validateEventExists(string $eventId): object
    {
        $event = $this->eventRepository->getById($eventId);

        if ($event === null) {
            throw new ResourceNotFoundException(['event_id' => 'Evento não encontrado.']);
        }

        return $event;
    }

    private function validateEventAvailability(object $event, int $quantity): void
    {
        if ($event->hasSoldOut() === true) {
            throw new EventCapacityExceededException;
        }

        if ($event->hasAvailableTickets($quantity) === false) {
            throw new TicketsPerEventLimitExceededException("A quantidade informada excede a quantidade de tickets disponíveis para o evento. Restam {$event->remainingTickets} tickets disponíveis.");
        }
    }

    private function validateParticipantTicketLimit(string $eventId, string $userId, int $quantity): void
    {
        $countSoldTicketsByParticipant = $this->orderRepository->getCountSoldTicketsByParticipant($eventId, $userId);
        $remainingTicketsPerParticipant = $this->configParams->maxTicketsPerEvent() - $countSoldTicketsByParticipant;

        if ($quantity > $remainingTicketsPerParticipant) {
            throw new TicketsPerEventLimitExceededException("A quantidade informada excede o seu limite de tickets para esse evento. Restam {$remainingTicketsPerParticipant} tickets disponíveis.");
        }
    }

    private function calculateDiscount(CreateOrderInputDto $createOrderInputDto, object $event): Money
    {
        $orderAmount = $createOrderInputDto->quantity * $event->ticketPrice->value();
        $discountCouponAmount = 0;

        if ($createOrderInputDto->discountCoupon !== null) {
            $discountCoupon = new DiscountCoupon($createOrderInputDto->discountCoupon);

            $discountCouponAmount = $orderAmount * $this->discountCouponRepository->getDiscountPercent($discountCoupon);
        }

        return new Money($discountCouponAmount);
    }

    private function calculateTotalAmount(int $quantity, object $event, Money $orderDiscount): Money
    {
        $orderAmount = $quantity * $event->ticketPrice->value();

        return new Money($orderAmount - $orderDiscount->value());
    }
}
