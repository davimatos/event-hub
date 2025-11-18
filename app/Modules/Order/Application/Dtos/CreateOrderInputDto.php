<?php

namespace App\Modules\Order\Application\Dtos;

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
