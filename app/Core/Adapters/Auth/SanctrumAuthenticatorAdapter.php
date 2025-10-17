<?php

namespace App\Core\Adapters\Auth;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
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

    public function getAuthUser() : ?User
    {
        $authUserModel = Auth::user();

        if (null === $authUserModel) {
            return null;
        }

        return new User(
            $authUserModel->id,
            $authUserModel->name,
            new Email($authUserModel->email),
            UserType::from($authUserModel->type),
            null,
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
