<?php

namespace App\Modules\Shared\Domain\Adapters;

use App\Modules\User\Domain\Entities\User;

interface AuthenticatorAdapterInterface
{
    public function checkCredentials(string $email, string $password): bool;

    public function getAuthUser(): ?User;

    public function generateToken(): string;
}
