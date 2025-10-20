<?php

namespace App\Modules\User\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

final readonly class Email
{
    private string $address;

    public function __construct(string $address)
    {
        $this->address = trim(strtolower($address));
        $this->validate();
    }

    private function validate(): void
    {
        $this->validateEmail();
    }

    private function validateEmail(): void
    {
        if (filter_var($this->address, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException(['email' => 'Endereço de email inválido.']);
        }
    }

    public function __toString(): string
    {
        return $this->address;
    }
}
