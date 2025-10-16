<?php

namespace App\Modules\User\Infra\Repositories;

use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Infra\Models\UserModel;

class UserEloquentRepository implements UserRepositoryInterface
{
    public function getByEmail(string $email): ?User
    {
        $userModel = UserModel::where('email', $email)->first();

        if (null === $userModel) {
            return null;
        }

        return new User(
            $userModel->id,
            $userModel->name,
            new Email($userModel->email),
            $userModel->created_at,
            $userModel->updated_at
        );
    }
}
