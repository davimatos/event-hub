<?php

namespace App\Modules\User\Infra\Repositories;

use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Infra\Models\UserModel;

class UserEloquentRepository implements UserRepositoryInterface
{
    public function getByEmail(string $email): ?User
    {
        $userModel = UserModel::where('email', $email)->first();

        if ($userModel === null) {
            return null;
        }

        return new User(
            $userModel->id,
            $userModel->name,
            new Email($userModel->email),
            UserType::from($userModel->type),
            null,
            $userModel->created_at,
            $userModel->updated_at
        );
    }

    public function create(User $user): User
    {
        $userModel = new UserModel([
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type->value,
            'password' => $user->password,
        ]);

        $userModel->save();

        return new User(
            $userModel->id,
            $userModel->name,
            new Email($userModel->email),
            UserType::from($userModel->type),
            null,
            $userModel->created_at,
            $userModel->updated_at
        );
    }
}
