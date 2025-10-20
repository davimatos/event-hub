<?php

namespace App\Modules\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

final class Money
{
    private float $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
        $this->validate();
    }

    private function validate(): void
    {
        $this->validateAmount();
    }

    private function validateAmount(): void
    {
        if ($this->amount < 0) {
            throw new ValidationException(['*' => 'O valor monetário não pode ser negativo.']);
        }
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }

    public function value(): float
    {
        return $this->amount;
    }
}
