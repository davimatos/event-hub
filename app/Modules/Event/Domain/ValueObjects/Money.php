<?php

namespace App\Modules\Event\Domain\ValueObjects;

use App\Framework\Exceptions\ValidationException;

final class Money
{
    private float $amount;

    public function __construct(float $amount)
    {
        if ($amount < 0) {
            throw new ValidationException(['*' => 'O valor monetário não pode ser negativo.']);
        }

        $this->amount = $amount;
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
