<?php

namespace App\Modules\Shared\Infra\Adapters;

use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Infra\Persistence\Eloquent\Mappers\UserMapper;
use Illuminate\Support\Facades\Auth;

class SanctrumAuthenticatorAdapter implements AuthenticatorAdapterInterface
{
    public function checkCredentials(string $email, string $password): bool
    {
        return Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function getAuthUser(): ?User
    {
        $authUserModel = Auth::user();

        if ($authUserModel === null) {
            return null;
        }

        return UserMapper::toEntity($authUserModel);
    }

    public function generateToken(): string
    {
        $authUserModel = Auth::user();

        return $authUserModel->createToken('auth_token')->plainTextToken;
    }
}
