<?php

namespace App\Modules\User\Domain\UseCases;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Core\Exceptions\UnauthorizedException;
use App\Modules\User\Domain\Dtos\CreateUserInputDto;
use App\Modules\User\Domain\Dtos\UserOutputDto;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Exceptions\EmailAlreadyExistsException;
use App\Modules\User\Domain\Exceptions\PasswordConfirmationMismatchException;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;

class CreateUserUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserInputDto $createUserInputDto): UserOutputDto
    {
        if ($createUserInputDto->password !== $createUserInputDto->password_confirmation) {
            throw new PasswordConfirmationMismatchException;
        }

        if ($this->userRepository->getByEmail($createUserInputDto->email) !== null) {
            throw new EmailAlreadyExistsException;
        }

        $authUser = $this->authenticator->getAuthUser();
        $typeToCreate = $createUserInputDto->type ? UserType::from($createUserInputDto->type) : UserType::PARTICIPANT;

        if ($authUser === null) {
            $typeToCreate = UserType::PARTICIPANT;
        } elseif ($typeToCreate === UserType::ORGANIZER && $authUser->canCreateOrganizerUser() === false) {
            throw new UnauthorizedException;
        }

        $user = new User(
            id: null,
            name: $createUserInputDto->name,
            email: new Email($createUserInputDto->email),
            type: $typeToCreate,
            password: new Password($createUserInputDto->password),
        );

        $newUser = $this->userRepository->create($user);

        return UserOutputDto::fromEntity($newUser);
    }
}
