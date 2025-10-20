<?php

namespace App\Modules\Order\Domain\Entities;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

readonly class CreditCard
{
    public function __construct(
        public string $number,
        public string $holderName,
        public string $expirationDate,
        public string $cvv,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $this->validateCardNumber();
        $this->validateCvv();
        $this->validateExpirationDate();
    }

    private function validateCardNumber(): void
    {
        if (strlen($this->number) !== 16) {
            throw new ValidationException(['card_number' => 'O número do cartão deve ter 16 dígitos.']);
        }
    }

    private function validateCvv(): void
    {
        if (strlen($this->cvv) !== 3) {
            throw new ValidationException(['card_cvv' => 'O CVV deve ter 3 dígitos.']);
        }
    }

    private function validateExpirationDate(): void
    {
        if (! preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $this->expirationDate)) {
            throw new ValidationException(['card_expiration_date' => 'A data de validade deve estar no formato MM/YY.']);
        }

        [$month, $year] = explode('/', $this->expirationDate);

        $cardMonth = (int) $month;
        $cardYear = (int) ('20'.$year);

        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        if ($cardYear < $currentYear || ($cardYear === $currentYear && $cardMonth < $currentMonth)) {
            throw new ValidationException(['card_expiration_date' => 'A data de validade do cartão está expirada.']);
        }
    }
}
