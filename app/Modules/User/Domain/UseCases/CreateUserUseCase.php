<?php

namespace App\Modules\User\Domain\UseCases;

use App\Core\Adapters\Auth\Contracts\AuthenticatorAdapterInterface;
use App\Modules\User\Domain\Dtos\CreateUserInputDto;
use App\Modules\User\Domain\Dtos\UserOutputDto;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Exceptions\EmailAlreadyExistsException;
use App\Modules\User\Domain\Exceptions\PasswordConfirmationMismatchException;
use App\Modules\User\Domain\Exceptions\UnauthorizedUserException;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;

class CreateUserUseCase
{
    function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserInputDto $createUserInputDto): UserOutputDto
    {
        if ($createUserInputDto->password !== $createUserInputDto->password_confirmation) {
            throw new PasswordConfirmationMismatchException();
        }

        if (null !== $this->userRepository->getByEmail($createUserInputDto->email)) {
            throw new EmailAlreadyExistsException();
        }

        $authUser = $this->authenticator->getAuthUser();
        $typeToCreate = $createUserInputDto->type ? UserType::from($createUserInputDto->type) : UserType::PARTICIPANT;

        if (null === $authUser) {
            $typeToCreate = UserType::PARTICIPANT;
        } else if ($typeToCreate === UserType::ORGANIZER && false === $authUser->canCreateOrganizerUser()) {
            throw new UnauthorizedUserException();
        }

        $user = new User(
            id: null,
            name: $createUserInputDto->name,
            email: new Email($createUserInputDto->email),
            type: $typeToCreate,
            password: new Password($createUserInputDto->password),
        );

        $newUser = $this->userRepository->create($user);

        return new UserOutputDto(
            $newUser->id,
            $newUser->name,
            $newUser->email,
            $newUser->type->value,
            $newUser->created_at,
            $newUser->updated_at
        );
    }
}
