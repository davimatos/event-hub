<?php

namespace App\Modules\User\Infra\Persistence\Eloquent\Mappers;

use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;

class UserMapper
{
    public static function toEntity(UserModel $userModel): User
    {
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

    public static function toPersistence(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type->value,
            'password' => $user->password,
        ];
    }
}
