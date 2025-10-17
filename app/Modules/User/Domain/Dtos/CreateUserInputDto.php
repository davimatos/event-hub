<?php

namespace App\Modules\User\Domain\Dtos;

readonly class CreateUserInputDto
{
    function __construct(
        public string $name,
        public string $email,
        public ?int $type = null,
        public string $password,
        public string $password_confirmation,
    ) {}
}
