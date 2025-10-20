<?php

namespace App\Modules\Order\Domain\Entities;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

readonly class DiscountCoupon
{
    public function __construct(
        public string $code,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->code)) {
            throw new ValidationException(['discount_coupon' => 'O código do cupom não pode estar vazio.']);
        }

        if (strlen($this->code) < 3) {
            throw new ValidationException(['discount_coupon' => 'O código do cupom deve ter no mínimo 3 caracteres.']);
        }

        if (strlen($this->code) > 50) {
            throw new ValidationException(['discount_coupon' => 'O código do cupom deve ter no máximo 50 caracteres.']);
        }
    }
}
