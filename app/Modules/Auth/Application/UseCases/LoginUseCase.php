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
        $authUser = $this->validateUserExists($loginInputDto->email);
        $this->validateCredentials($authUser->email, $loginInputDto->password);

        $authToken = $this->authenticator->generateToken();

        return new LoginOutputDto($authToken, 'Bearer', $this->configParams->authTokenLifetimeInMinutes());
    }

    private function validateUserExists(string $email): object
    {
        $authUser = $this->userRepository->getByEmail($email);

        if ($authUser === null) {
            throw new InvalidCredentialsException;
        }

        return $authUser;
    }

    private function validateCredentials(string $email, string $password): void
    {
        $isValidCredentials = $this->authenticator->checkCredentials($email, $password);

        if ($isValidCredentials === false) {
            throw new InvalidCredentialsException;
        }
    }
}
