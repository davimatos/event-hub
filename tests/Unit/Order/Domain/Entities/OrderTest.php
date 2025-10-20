<?php

namespace Tests\Unit\Order\Domain\Entities;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Order\Domain\Entities\Order;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class OrderTest extends TestCase
{
    private function createValidUser(): User
    {
        return new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::PARTICIPANT
        );
    }

    private function createValidOrganizer(): User
    {
        return new User(
            id: 'organizer_123',
            name: 'Maria Organizadora',
            email: new Email('maria@org.com'),
            type: UserType::ORGANIZER
        );
    }

    private function createValidEvent(): Event
    {
        return new Event(
            id: 'event_123',
            organizer: $this->createValidOrganizer(),
            title: 'Evento Tech 2025',
            description: 'Evento de tecnologia',
            date: new Date('2025-12-31'),
            ticketPrice: new Money(100.00),
            capacity: 500,
            remainingTickets: 500
        );
    }

    public function test_create_order_successfully()
    {
        $order = new Order(
            id: 'order_123',
            event: $this->createValidEvent(),
            participant: $this->createValidUser(),
            quantity: 3,
            ticketPrice: new Money(100.00),
            discount: new Money(10.00),
            totalAmount: new Money(290.00),
            status: OrderStatus::CONFIRMED,
            tickets: [],
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('order_123', $order->id);
        $this->assertEquals(3, $order->quantity);
        $this->assertEquals(new Money(100.00), $order->ticketPrice);
        $this->assertEquals(new Money(10.00), $order->discount);
        $this->assertEquals(new Money(290.00), $order->totalAmount);
        $this->assertEquals(OrderStatus::CONFIRMED, $order->status);
        $this->assertEquals([], $order->tickets);
        $this->assertEquals('2025-01-01 10:00:00', $order->createdAt);
        $this->assertEquals('2025-01-01 10:00:00', $order->updatedAt);
    }

    public function test_order_quantity_must_be_greater_than_zero()
    {
        try {
            new Order(
                id: 'order_123',
                event: $this->createValidEvent(),
                participant: $this->createValidUser(),
                quantity: 0,
                ticketPrice: new Money(100.00),
                discount: new Money(0.00),
                totalAmount: new Money(0.00),
                status: OrderStatus::PENDING
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('quantity', $context);
            $this->assertEquals('A quantidade deve ser maior que zero.', $context['quantity']);
        }
    }

    public function test_discount_cannot_be_greater_than_total_amount()
    {
        try {
            new Order(
                id: 'order_123',
                event: $this->createValidEvent(),
                participant: $this->createValidUser(),
                quantity: 2,
                ticketPrice: new Money(100.00),
                discount: new Money(250.00),
                totalAmount: new Money(200.00),
                status: OrderStatus::PENDING
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('discount', $context);
            $this->assertEquals('O desconto não pode ser maior que o valor total.', $context['discount']);
        }
    }
}
