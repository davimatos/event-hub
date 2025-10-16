<?php

namespace App\Core\Adapters\Auth;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Modules\User\Domain\Entities\User;
use Illuminate\Support\Facades\Auth;

class SanctrumAuthenticatorAdapter implements AuthenticatorAdapterInterface
{

    public function checkCredentials(string $email, string $password): bool
    {
        return Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password
        ]);
    }

    public function getAuthUser() : User
    {
        $authUserModel = Auth::user();

        return new User(
            $authUserModel->id,
            $authUserModel->name,
            $authUserModel->email,
            $authUserModel->created_at,
            $authUserModel->updated_at
        );
    }

    public function generateToken(): string
    {
        $authUserModel = Auth::user();

        return $authUserModel->createToken('auth_token')->plainTextToken;
    }
}
