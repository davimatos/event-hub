<?php

namespace App\Modules\User\Infra\Persistence\Eloquent\Repositories;

use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Infra\Persistence\Eloquent\Mappers\UserMapper;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;

class UserEloquentRepository implements UserRepositoryInterface
{
    public function getByEmail(string $email): ?User
    {
        $userModel = UserModel::where('email', $email)->first();

        if ($userModel === null) {
            return null;
        }

        return UserMapper::toEntity($userModel);
    }

    public function create(User $user): User
    {
        $userModel = new UserModel(UserMapper::toPersistence($user));

        $userModel->save();

        return UserMapper::toEntity($userModel);
    }
}
