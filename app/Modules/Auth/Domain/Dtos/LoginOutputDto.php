<?php

namespace App\Modules\Auth\Domain\Dtos;

readonly class LoginOutputDto
{
    public function __construct(
        public string $token,
        public string $tokenType = 'Bearer',
        public int $expiresIn = 3600,
    )
    {}
}
