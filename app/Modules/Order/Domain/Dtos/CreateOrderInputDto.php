<?php

namespace App\Modules\Order\Domain\Dtos;

readonly class CreateOrderInputDto
{
    public function __construct(
        public string $eventId,
        public int $quantity,
    ) {}
}
