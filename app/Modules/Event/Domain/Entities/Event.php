<?php

namespace App\Modules\Event\Domain\Entities;

use App\Core\Exceptions\ValidationException;
use App\Modules\Event\Domain\ValueObjects\Date;
use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\User\Domain\Entities\User;

class Event
{
    public function __construct(
        public ?string $id,
        public User $organizer,
        public string $title,
        public string $description,
        public Date $date,
        public Money $ticketPrice,
        public int $capacity,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
        $this->validate();
    }

    public function validate(): void
    {
        if ($this->capacity <= 0) {
            throw new ValidationException(['capacity' => 'A capacidade total deve ser maior que zero.']);
        }

        if (filter_var($this->capacity, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException(['capacity' => 'A capacidade total deve ser um número inteiro.']);
        }

        $eventDate = new \DateTimeImmutable($this->date);
        $todayDate = new \DateTimeImmutable('today');

        if ($eventDate < $todayDate) {
            throw new ValidationException(['date' => 'A data do evento não pode ser no passado.']);
        }
    }
}
