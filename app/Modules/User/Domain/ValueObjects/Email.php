<?php

namespace App\Modules\User\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

final readonly class Email
{
    private string $address;

    public function __construct(string $address)
    {
        $address = trim(strtolower($address));

        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException(['email' => 'Endereço de email inválido.']);
        }

        $this->address = $address;
    }

    public function __toString(): string
    {
        return $this->address;
    }
}
