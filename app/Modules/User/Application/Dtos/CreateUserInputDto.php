<?php

namespace App\Modules\User\Application\Dtos;

readonly class CreateUserInputDto
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $type,
        public string $password,
    ) {}
}
