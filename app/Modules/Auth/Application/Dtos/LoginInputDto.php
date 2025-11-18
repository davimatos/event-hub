<?php

namespace App\Modules\Auth\Application\Dtos;

readonly class LoginInputDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
