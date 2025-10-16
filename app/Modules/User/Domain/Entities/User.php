<?php

namespace App\Modules\User\Domain\Entities;

use App\Modules\User\Domain\ValueObjects\Email;

class User
{
    public function __construct(
        public string $id,
        public string $name,
        public Email $email,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
