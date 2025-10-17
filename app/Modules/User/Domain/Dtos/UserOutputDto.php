<?php

namespace App\Modules\User\Domain\Dtos;

readonly class UserOutputDto
{
    function __construct(
        public string $id,
        public string $name,
        public string $email,
        public int $type,
        public string $created_at,
        public string $updated_at,
    ) {}
}
