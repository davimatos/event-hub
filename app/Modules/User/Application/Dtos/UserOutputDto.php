<?php

namespace App\Modules\User\Application\Dtos;

use App\Modules\User\Domain\Entities\User;

readonly class UserOutputDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $type,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            type: $user->type->value,
            created_at: $user->createdAt,
            updated_at: $user->updatedAt,
        );
    }
}
