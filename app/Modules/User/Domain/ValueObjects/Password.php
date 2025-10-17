<?php

namespace App\Modules\User\Domain\ValueObjects;

use App\Core\Exceptions\ValidationException;

readonly final class Password
{
    const MIN_LENGTH = 8;

    private string $password;

    function __construct(string $password)
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new ValidationException(['password' => 'A senha deve ter no mínimo ' . self::MIN_LENGTH . ' caracteres.']);
        }

        $this->password = $password;
    }

    public function __toString(): string
    {
        return $this->password;
    }
}
