<?php

namespace Tests\Unit\Order\Application\UseCases;

use App\Modules\Event\Application\Exceptions\EventCapacityExceededException;
use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Order\Application\Exceptions\OrderPaymentFailException;
use App\Modules\Order\Application\Exceptions\TicketsPerEventLimitExceededException;
use App\Modules\Order\Application\Exceptions\TicketsPerOrderLimitExceededException;
use App\Modules\Order\Application\Services\NewOrderNotificationServiceInterface;
use App\Modules\Order\Application\UseCases\CreateOrderUseCase;
use App\Modules\Order\Domain\Dtos\CreateOrderInputDto;
use App\Modules\Order\Domain\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Entities\CreditCard;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Entities\Ticket;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Order\Domain\Repositories\DiscountCouponRepositoryInterface;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\PaymentProcessor\Application\Services\Contract\PaymentProcessorServiceInterface;
use App\Modules\Shared\Application\Exceptions\ResourceNotFoundException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;
use App\Modules\Shared\Domain\Repositories\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class CreateOrderUseCaseTest extends TestCase
{
    private AuthenticatorAdapterInterface $authenticator;
    private ConfigParamsRepositoryInterface $configParams;
    private OrderRepositoryInterface $orderRepository;
    private EventRepositoryInterface $eventRepository;
    private TransactionManagerInterface $transactionManager;
    private DiscountCouponRepositoryInterface $discountCouponRepository;
    private PaymentProcessorServiceInterface $paymentProcessor;
    private NewOrderNotificationServiceInterface $newOrderNotification;
    private CreateOrderUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticator = $this->createMock(AuthenticatorAdapterInterface::class);
        $this->configParams = $this->createMock(ConfigParamsRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->eventRepository = $this->createMock(EventRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->discountCouponRepository = $this->createMock(DiscountCouponRepositoryInterface::class);
        $this->paymentProcessor = $this->createMock(PaymentProcessorServiceInterface::class);
        $this->newOrderNotification = $this->createMock(NewOrderNotificationServiceInterface::class);

        $this->useCase = new CreateOrderUseCase(
            $this->authenticator,
            $this->configParams,
            $this->orderRepository,
            $this->eventRepository,
            $this->transactionManager,
            $this->discountCouponRepository,
            $this->paymentProcessor,
            $this->newOrderNotification
        );
    }

    private function createParticipantUser(): User
    {
        return new User(
            id: 'participant_123',
            name: 'João Silva',
            email: new Email('joao@example.com'),
            type: UserType::PARTICIPANT,
            password: null,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    private function createOrganizerUser(): User
    {
        return new User(
            id: 'organizer_123',
            name: 'Maria Organizadora',
            email: new Email('maria@example.com'),
            type: UserType::ORGANIZER,
            password: null,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    private function createValidEvent(): Event
    {
        return new Event(
            id: 'event_123',
            organizer: $this->createOrganizerUser(),
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 500,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    public function test_create_order_successfully()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->configParams
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->eventRepository
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->eventRepository
            ->method('getRemainingTickets')
            ->with('event_123')
            ->willReturn(500);

        $this->eventRepository
            ->method('decrementRemainingTickets')
            ->with('event_123', 2);

        $this->orderRepository
            ->method('getCountSoldTicketsByParticipant')
            ->with('event_123', 'participant_123')
            ->willReturn(0);

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 2,
            ticketPrice: new Money(100.00),
            discount: new Money(0.00),
            totalAmount: new Money(200.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->orderRepository
            ->method('create')
            ->willReturn($createdOrder);

        $this->paymentProcessor
            ->method('process')
            ->with(
                $this->isInstanceOf(Order::class),
                $this->isInstanceOf(CreditCard::class),
            )
            ->willReturn(true);

        $this->newOrderNotification
            ->method('execute')
            ->with($this->isInstanceOf(Order::class));

        $this->transactionManager
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder, &$newOrder) {
                $newOrder = $createdOrder;
                $callback();
                return $createdOrder;
            });

        $result = $this->useCase->execute($inputDto);

        $this->assertInstanceOf(OrderOutputDto::class, $result);
        $this->assertEquals('order_123', $result->id);
        $this->assertEquals(2, $result->quantity);
        $this->assertEquals(100.00, $result->ticket_price);
        $this->assertEquals(0.00, $result->discount);
        $this->assertEquals(200.00, $result->total_amount);
        $this->assertEquals('confirmed', $result->status);
    }

    public function test_create_order_with_invalid_quantity_throws_exception()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 0,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(5);

        $event = $this->createValidEvent();

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->transactionManager
            ->expects($this->never())
            ->method('run');

        $this->expectException(ValidationException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_order_exceeding_event_capacity_throws_exception()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 10,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();

        $event = new Event(
            id: 'event_123',
            organizer: $this->createOrganizerUser(),
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 5,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->transactionManager
            ->expects($this->never())
            ->method('run');

        try {
            $this->useCase->execute($inputDto);
        } catch (TicketsPerEventLimitExceededException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('quantity', $context);
            $this->assertStringContainsString('5 tickets disponíveis.', $context['quantity']);
        }
    }

    public function test_create_order_exceeding_per_order_limit_throws_exception()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 15,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->never())
            ->method('getById');

        $this->transactionManager
            ->expects($this->never())
            ->method('run');

        $this->expectException(TicketsPerOrderLimitExceededException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_order_with_valid_discount_coupon_applies_discount()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123',
            discountCoupon: 'PROMO10'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->expects($this->once())
            ->method('getCountSoldTicketsByParticipant')
            ->with('event_123', 'participant_123')
            ->willReturn(0);

        $this->discountCouponRepository
            ->expects($this->once())
            ->method('getDiscountPercent')
            ->willReturn(0.10);

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 2,
            ticketPrice: new Money(100.00),
            discount: new Money(20.00),
            totalAmount: new Money(180.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder) {
                $callback();
                return $createdOrder;
            });

        $this->paymentProcessor
            ->expects($this->once())
            ->method('process')
            ->willReturn(true);

        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdOrder);

        $this->newOrderNotification
            ->expects($this->once())
            ->method('execute');

        $result = $this->useCase->execute($inputDto);

        $this->assertEquals(20.00, $result->discount);
        $this->assertEquals(180.00, $result->total_amount);
    }

    public function test_create_order_with_invalid_discount_coupon_ignores_discount()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123',
            discountCoupon: 'INVALID'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->expects($this->once())
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $this->discountCouponRepository
            ->expects($this->once())
            ->method('getDiscountPercent')
            ->willReturn(0.00);

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 2,
            ticketPrice: new Money(100.00),
            discount: new Money(0.00),
            totalAmount: new Money(200.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder) {
                $callback();
                return $createdOrder;
            });

        $this->paymentProcessor
            ->expects($this->once())
            ->method('process')
            ->willReturn(true);

        $this->orderRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdOrder);

        $this->newOrderNotification
            ->expects($this->once())
            ->method('execute');

        $result = $this->useCase->execute($inputDto);

        $this->assertEquals(0.00, $result->discount);
        $this->assertEquals(200.00, $result->total_amount);
    }

    public function test_create_order_with_invalid_credit_card_throws_exception()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '123',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->expects($this->once())
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $this->transactionManager
            ->expects($this->never())
            ->method('run');

        try {
            $this->useCase->execute($inputDto);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('card_number', $context);
            $this->assertEquals('O número do cartão deve ter 16 dígitos.', $context['card_number']);
        }
    }

    public function test_create_order_payment_failure_throws_exception()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->with('event_123')
            ->willReturn($event);

        $this->configParams
            ->expects($this->once())
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->expects($this->once())
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        $this->paymentProcessor
            ->expects($this->once())
            ->method('process')
            ->willReturn(false);

        $this->orderRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(OrderPaymentFailException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_order_generates_tickets()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 3,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->method('getById')
            ->willReturn($event);

        $this->configParams
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $tickets = [
            new Ticket('ticket_1', 'order_123', 'event_123', 'participant_123', null, '2025-10-20 15:00:00', '2025-10-20 15:00:00'),
            new Ticket('ticket_2', 'order_123', 'event_123', 'participant_123', null, '2025-10-20 15:00:00', '2025-10-20 15:00:00'),
            new Ticket('ticket_3', 'order_123', 'event_123', 'participant_123', null, '2025-10-20 15:00:00', '2025-10-20 15:00:00'),
        ];

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 3,
            ticketPrice: new Money(100.00),
            discount: new Money(0.00),
            totalAmount: new Money(300.00),
            status: OrderStatus::CONFIRMED,
            tickets: $tickets,
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->transactionManager
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder) {
                $callback();
                return $createdOrder;
            });

        $this->paymentProcessor
            ->method('process')
            ->willReturn(true);

        $this->orderRepository
            ->method('create')
            ->willReturn($createdOrder);

        $this->newOrderNotification
            ->method('execute');

        $result = $this->useCase->execute($inputDto);

        $this->assertCount(3, $result->tickets);
        $this->assertEquals(3, $result->quantity);
    }

    public function test_create_order_updates_remaining_tickets()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 5,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();

        $event = new Event(
            id: 'event_123',
            organizer: $this->createOrganizerUser(),
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 500,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->authenticator
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->method('getById')
            ->willReturn($event);

        $this->configParams
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 5,
            ticketPrice: new Money(100.00),
            discount: new Money(0.00),
            totalAmount: new Money(500.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $decrementCalled = false;
        $decrementEventId = null;
        $decrementQuantity = null;
        $initialRemainingTickets = $event->remainingTickets;

        $this->eventRepository
            ->method('decrementRemainingTickets')
            ->willReturnCallback(function ($eventId, $quantity) use (&$decrementCalled, &$decrementEventId, &$decrementQuantity) {
                $decrementCalled = true;
                $decrementEventId = $eventId;
                $decrementQuantity = $quantity;
            });

        $this->transactionManager
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder) {
                $callback();
                return $createdOrder;
            });

        $this->paymentProcessor
            ->method('process')
            ->willReturn(true);

        $this->orderRepository
            ->method('create')
            ->willReturn($createdOrder);

        $this->newOrderNotification
            ->method('execute');

        $result = $this->useCase->execute($inputDto);

        $this->assertTrue($decrementCalled);
        $this->assertEquals('event_123', $decrementEventId);
        $this->assertEquals(5, $decrementQuantity);

        $this->assertEquals(500, $initialRemainingTickets);
        $expectedRemainingTickets = $initialRemainingTickets - $decrementQuantity;
        $this->assertEquals(495, $expectedRemainingTickets);

        $this->assertInstanceOf(OrderOutputDto::class, $result);
        $this->assertEquals('order_123', $result->id);
        $this->assertEquals(5, $result->quantity);
    }

    public function test_create_order_sends_notifications()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->method('getById')
            ->willReturn($event);

        $this->configParams
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $createdOrder = new Order(
            id: 'order_123',
            event: $event,
            participant: $participant,
            quantity: 2,
            ticketPrice: new Money(100.00),
            discount: new Money(0.00),
            totalAmount: new Money(200.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->transactionManager
            ->method('run')
            ->willReturnCallback(function ($callback) use ($createdOrder) {
                $callback();
                return $createdOrder;
            });

        $this->paymentProcessor
            ->method('process')
            ->willReturn(true);

        $this->orderRepository
            ->method('create')
            ->willReturn($createdOrder);

        $this->newOrderNotification
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($order) {
                return $order instanceof Order && $order->id === 'order_123';
            }));

        $this->useCase->execute($inputDto);
    }

    public function test_create_order_rollback_on_payment_failure()
    {
        $inputDto = new CreateOrderInputDto(
            eventId: 'event_123',
            quantity: 2,
            cardNumber: '1234567812345678',
            cardHolderName: 'JOAO SILVA',
            cardExpirationDate: '12/26',
            cardCvv: '123'
        );

        $participant = $this->createParticipantUser();
        $event = $this->createValidEvent();

        $this->authenticator
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->configParams
            ->method('maxTicketsPerOrder')
            ->willReturn(10);

        $this->eventRepository
            ->method('getById')
            ->willReturn($event);

        $this->configParams
            ->method('maxTicketsPerEvent')
            ->willReturn(50);

        $this->orderRepository
            ->method('getCountSoldTicketsByParticipant')
            ->willReturn(0);

        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        $this->paymentProcessor
            ->method('process')
            ->willReturn(false);

        $this->orderRepository
            ->expects($this->never())
            ->method('create');

        $this->eventRepository
            ->expects($this->never())
            ->method('decrementRemainingTickets');

        $this->newOrderNotification
            ->expects($this->never())
            ->method('execute');

        $this->expectException(OrderPaymentFailException::class);

        $this->useCase->execute($inputDto);
    }
}

