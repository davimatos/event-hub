<?php

namespace Tests\Unit\Event\Application\UseCases;

use App\Modules\Event\Application\UseCases\CreateEventUseCase;
use App\Modules\Event\Domain\Dtos\CreateEventInputDto;
use App\Modules\Event\Domain\Dtos\EventOutputDto;
use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Shared\Application\Exceptions\UnauthorizedException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class CreateEventUseCaseTest extends TestCase
{
    private AuthenticatorAdapterInterface $authenticator;
    private EventRepositoryInterface $eventRepository;
    private CreateEventUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticator = $this->createMock(AuthenticatorAdapterInterface::class);
        $this->eventRepository = $this->createMock(EventRepositoryInterface::class);

        $this->useCase = new CreateEventUseCase(
            $this->authenticator,
            $this->eventRepository
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

    private function createParticipantUser(): User
    {
        return new User(
            id: 'participant_123',
            name: 'João Participante',
            email: new Email('joao@example.com'),
            type: UserType::PARTICIPANT,
            password: null,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    public function test_create_event_successfully()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '2025-12-31',
            ticketPrice: 150.00,
            capacity: 500
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $createdEvent = new Event(
            id: 'event_123',
            organizer: $organizerUser,
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(150.00),
            capacity: 500,
            remainingTickets: 500,
            createdAt: '2025-10-20 14:30:00',
            updatedAt: '2025-10-20 14:30:00'
        );

        $this->eventRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdEvent);

        $result = $this->useCase->execute($inputDto);

        $this->assertInstanceOf(EventOutputDto::class, $result);
        $this->assertEquals('event_123', $result->id);
        $this->assertEquals('Tech Conference 2025', $result->title);
        $this->assertEquals('Conferência de tecnologia', $result->description);
        $this->assertEquals(150.00, $result->ticket_price);
        $this->assertEquals(500, $result->capacity);
        $this->assertEquals(500, $result->remaining_tickets);
        $this->assertEquals('2025-10-20 14:30:00', $result->created_at);
        $this->assertEquals('2025-10-20 14:30:00', $result->updated_at);
    }

    public function test_create_event_with_participant_user_throws_exception()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '9999-99-99',
            ticketPrice: 150.00,
            capacity: 500
        );

        $participant = $this->createParticipantUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participant);

        $this->eventRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(UnauthorizedException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_event_with_invalid_date_throws_exception()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '9999-99-99',
            ticketPrice: 150.00,
            capacity: 500
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $this->eventRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(ValidationException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_event_with_past_date_throws_exception()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '2024-01-01',
            ticketPrice: 150.00,
            capacity: 500
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $this->eventRepository
            ->expects($this->never())
            ->method('create');

        try {
            $this->useCase->execute($inputDto);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('date', $context);
            $this->assertEquals('A data do evento não pode ser no passado.', $context['date']);
        }
    }

    public function test_create_event_with_zero_capacity_throws_exception()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '2025-12-31',
            ticketPrice: 150.00,
            capacity: 0
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $this->eventRepository
            ->expects($this->never())
            ->method('create');

        try {
            $this->useCase->execute($inputDto);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('capacity', $context);
            $this->assertEquals('A capacidade total deve ser maior que zero.', $context['capacity']);
        }
    }

    public function test_create_event_with_negative_price_throws_exception()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '2025-12-31',
            ticketPrice: -50.00,
            capacity: 500
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $this->eventRepository
            ->expects($this->never())
            ->method('create');

        try {
            $this->useCase->execute($inputDto);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('*', $context);
            $this->assertEquals('O valor monetário não pode ser negativo.', $context['*']);
        }
    }

    public function test_create_event_sets_remaining_tickets_equal_to_capacity()
    {
        $inputDto = new CreateEventInputDto(
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: '2025-12-31',
            ticketPrice: 150.00,
            capacity: 300
        );

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $createdEvent = new Event(
            id: 'event_456',
            organizer: $organizerUser,
            title: 'Tech Conference 2025',
            description: 'Conferência de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(150.00),
            capacity: 300,
            remainingTickets: 300,
            createdAt: '2025-10-20 15:00:00',
            updatedAt: '2025-10-20 15:00:00'
        );

        $this->eventRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($event) {
                return $event instanceof Event
                    && $event->capacity === 300
                    && $event->remainingTickets === 300
                    && $event->capacity === $event->remainingTickets;
            }))
            ->willReturn($createdEvent);

        $result = $this->useCase->execute($inputDto);

        $this->assertEquals(300, $result->capacity);
        $this->assertEquals(300, $result->remaining_tickets);
        $this->assertEquals($result->capacity, $result->remaining_tickets);
    }
}
