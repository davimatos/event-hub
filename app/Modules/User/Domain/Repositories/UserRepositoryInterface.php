<?php

namespace App\Modules\User\Domain\Repositories;

use App\Modules\User\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function getByEmail(string $email) : ?User;
    public function create(User $user) : User;
}
