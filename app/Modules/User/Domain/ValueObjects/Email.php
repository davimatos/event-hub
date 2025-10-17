<?php

namespace App\Modules\User\Domain\ValueObjects;

use App\Core\Exceptions\ValidationException;

readonly final class Email
{
    private string $address;

    function __construct(string $address)
    {
        $address = trim(strtolower($address));

        if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => 'Endereço de email inválido.']);
        }

        $this->address = $address;
    }

    public function __toString(): string
    {
        return $this->address;
    }
}
