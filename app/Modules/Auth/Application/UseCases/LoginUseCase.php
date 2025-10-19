<?php

namespace App\Modules\Auth\Application\UseCases;

use App\Modules\Auth\Application\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Domain\Dtos\LoginInputDto;
use App\Modules\Auth\Domain\Dtos\LoginOutputDto;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;

readonly class LoginUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private ConfigParamsRepositoryInterface $configParams,
        private UserRepositoryInterface $userRepository,
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

        return new LoginOutputDto($authToken, 'Bearer', $this->configParams->authTokenLifetimeInMinutes());
    }
}
