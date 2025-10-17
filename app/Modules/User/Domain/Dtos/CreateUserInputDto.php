<?php

namespace App\Modules\User\Domain\Dtos;

readonly class CreateUserInputDto
{
    public function __construct(
        public string $name,
        public string $email,
        public ?int $type,
        public string $password,
        public string $password_confirmation,
    ) {}
}
