<?php

namespace App\Modules\Auth\Domain\Dtos;

class LoginInputDto
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
