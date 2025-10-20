<?php

namespace Tests\Unit\Event\Domain\Entities;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class EventTest extends TestCase
{
    private function createValidUser(): User
    {
        return new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::ORGANIZER
        );
    }

    public function test_create_event_successfully()
    {
        $event = new Event(
            id: 'event_123',
            organizer: $this->createValidUser(),
            title: 'Evento legal 2025',
            description: 'Esse é um evento legal',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 500,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('event_123', $event->id);
        $this->assertEquals('Evento legal 2025', $event->title);
        $this->assertEquals('Esse é um evento legal', $event->description);
        $this->assertEquals(new Date('2025-12-31'), $event->date);
        $this->assertEquals(new Money(100.00), $event->ticketPrice);
        $this->assertEquals(500, $event->capacity);
        $this->assertEquals(500, $event->remainingTickets);
        $this->assertEquals('2025-01-01 10:00:00', $event->createdAt);
        $this->assertEquals('2025-01-01 10:00:00', $event->updatedAt);
    }

    public function test_event_capacity_must_be_greater_than_zero()
    {
        try {
            new Event(
                id: 'event_123',
                organizer: $this->createValidUser(),
                title: 'Evento legal 2025',
                description: 'Esse é um evento legal',
                date: new Date('2025-12-31'),
                ticketPrice: new Money(100.00),
                capacity: 0,
                remainingTickets: 0
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('capacity', $context);
            $this->assertEquals('A capacidade total deve ser maior que zero.', $context['capacity']);
        }
    }

    public function test_event_date_cannot_be_in_the_past()
    {
        try {
            new Event(
                id: 'event_123',
                organizer: $this->createValidUser(),
                title: 'Evento legal 2025',
                description: 'Esse é um evento legal',
                date: new Date('2024-01-01'),
                ticketPrice: new Money(100.00),
                capacity: 500,
                remainingTickets: 500
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('date', $context);
            $this->assertEquals('A data do evento não pode ser no passado.', $context['date']);
        }
    }

    public function test_has_available_tickets_returns_true_when_tickets_available()
    {
        $event = new Event(
            id: 'event_123',
            organizer: $this->createValidUser(),
            title: 'Evento legal 2025',
            description: 'Esse é um evento legal',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 100
        );

        $this->assertTrue($event->hasAvailableTickets(50));
        $this->assertTrue($event->hasAvailableTickets(100));
    }

    public function test_has_available_tickets_returns_false_when_not_enough_tickets()
    {
        $event = new Event(
            id: 'event_123',
            organizer: $this->createValidUser(),
            title: 'Evento legal 2025',
            description: 'Esse é um evento legal',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 50
        );

        $this->assertFalse($event->hasAvailableTickets(100));
        $this->assertFalse($event->hasAvailableTickets(51));
    }

    public function test_has_sold_out_returns_true_when_no_tickets_remaining()
    {
        $event = new Event(
            id: 'event_123',
            organizer: $this->createValidUser(),
            title: 'Evento legal 2025',
            description: 'Esse é um evento legal',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 0
        );

        $this->assertTrue($event->hasSoldOut());
    }

    public function test_has_sold_out_returns_false_when_tickets_available()
    {
        $event = new Event(
            id: 'event_123',
            organizer: $this->createValidUser(),
            title: 'Evento legal 2025',
            description: 'Esse é um evento legal',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 100
        );

        $this->assertFalse($event->hasSoldOut());
    }
}
