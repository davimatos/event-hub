<?php

namespace App\Modules\Auth\Domain\Dtos;

class LoginOutputDto
{
    public function __construct(
        public readonly string $token,
        public readonly string $tokenType = 'Bearer',
        public readonly int $expiresIn = 3600,
    )
    {}
}
