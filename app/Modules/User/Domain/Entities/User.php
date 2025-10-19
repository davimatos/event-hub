<?php

namespace App\Modules\User\Domain\Entities;

use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;

readonly class User
{
    public function __construct(
        public ?string $id,
        public string $name,
        public Email $email,
        public UserType $type,
        public ?Password $password = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    public function isOrganizer(): bool
    {
        return $this->type === UserType::ORGANIZER;
    }

    public function canCreateOrganizerUser(): bool
    {
        return $this->isOrganizer();
    }
}
