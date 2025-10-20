<?php

namespace App\Modules\User\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;

final readonly class Password
{
    const MIN_LENGTH = 8;

    private string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
        $this->validate();
    }

    private function validate(): void
    {
        $this->validateLength();
    }

    private function validateLength(): void
    {
        if (strlen($this->password) < self::MIN_LENGTH) {
            throw new ValidationException(['password' => 'A senha deve ter no mínimo '.self::MIN_LENGTH.' caracteres.']);
        }
    }

    public function __toString(): string
    {
        return $this->password;
    }
}
