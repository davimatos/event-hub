<?php

namespace App\Modules\Ticket\Domain\Entities;

readonly class Ticket
{
    public function __construct(
        public ?string $id,
        public ?string $orderId,
        public string $eventId,
        public string $participantId,
        public ?string $usedAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}
