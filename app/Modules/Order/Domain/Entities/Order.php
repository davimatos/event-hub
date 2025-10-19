<?php

namespace App\Modules\Order\Domain\Entities;

use App\Modules\Event\Domain\Entities\Event;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Order\Domain\Enums\OrderStatus;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\User\Domain\Entities\User;

class Order
{
    public function __construct(
        public ?string $id,
        public Event $event,
        public User $participant,
        public int $quantity,
        public Money $ticketPrice,
        public Money $discount,
        public Money $totalAmount,
        public OrderStatus $status,
        public ?array $tickets = [],
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->quantity <= 0) {
            throw new ValidationException(['quantity' => 'A quantidade deve ser maior que zero.']);
        }

        if (filter_var($this->quantity, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException(['quantity' => 'A quantidade deve ser um número inteiro.']);
        }

        if ($this->discount > $this->totalAmount) {
            throw new ValidationException(['discount' => 'O desconto não pode ser maior que o valor total.']);
        }
    }
}
