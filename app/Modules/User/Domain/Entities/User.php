<?php

namespace App\Modules\User\Domain\Entities;

use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;

class User
{
    public function __construct(
        public ?string $id = null,
        public string $name,
        public Email $email,
        public UserType $type,
        public ?string $password = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
