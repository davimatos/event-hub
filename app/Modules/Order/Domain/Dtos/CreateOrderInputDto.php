<?php

namespace App\Modules\Order\Domain\Dtos;

readonly class CreateOrderInputDto
{
    public function __construct(
        public string $eventId,
        public int $quantity,
        public string $cardNumber,
        public string $cardHolderName,
        public string $cardExpirationDate,
        public string $cardCvv,
        public ?string $discountCoupon = null,
    ) {}
}
