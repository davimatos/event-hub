<?php

namespace App\Modules\Auth\Domain\UseCases;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Core\Helpers\Params;
use App\Modules\Auth\Domain\Dtos\LoginInputDto;
use App\Modules\Auth\Domain\Dtos\LoginOutputDto;
use App\Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;

readonly class LoginUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private UserRepositoryInterface $userRepository,
        private Params $params
    ) {}

    public function execute(LoginInputDto $loginInputDto): LoginOutputDto
    {
        $authUser = $this->userRepository->getByEmail($loginInputDto->email);

        if ($authUser === null) {
            throw new InvalidCredentialsException;
        }

        $isValidCredentials = $this->authenticator->checkCredentials(
            $authUser->email,
            $loginInputDto->password
        );

        if ($isValidCredentials === false) {
            throw new InvalidCredentialsException;
        }

        $authToken = $this->authenticator->generateToken();

        return new LoginOutputDto($authToken, 'Bearer', $this->params::authTokenLifetimeInMinutes());
    }
}
