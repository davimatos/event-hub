<?php

namespace App\Modules\Event\Domain\Entities;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Date;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;

readonly class Event
{
    public function __construct(
        public ?string $id,
        public User $organizer,
        public string $title,
        public string $description,
        public Date $date,
        public Money $ticketPrice,
        public int $capacity,
        public int $remainingTickets,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
        $this->validate();
    }

    public function validate(): void
    {
        $this->validateCapacity();
        $this->validateEventDate();
    }

    private function validateCapacity(): void
    {
        if ($this->capacity <= 0) {
            throw new ValidationException(['capacity' => 'A capacidade total deve ser maior que zero.']);
        }
    }

    private function validateEventDate(): void
    {
        $eventDate = new \DateTimeImmutable($this->date);
        $todayDate = new \DateTimeImmutable('today');

        if ($eventDate < $todayDate) {
            throw new ValidationException(['date' => 'A data do evento não pode ser no passado.']);
        }
    }

    public function hasAvailableTickets(int $quantity): bool
    {
        return $this->remainingTickets >= $quantity;
    }

    public function hasSoldOut(): bool
    {
        return $this->remainingTickets <= 0;
    }
}
