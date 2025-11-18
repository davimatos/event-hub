<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\Shared\Application\Exceptions\UnauthorizedException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\User\Application\Dtos\CreateUserInputDto;
use App\Modules\User\Application\Dtos\UserOutputDto;
use App\Modules\User\Application\Exceptions\EmailAlreadyExistsException;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;

readonly class CreateUserUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserInputDto $createUserInputDto): UserOutputDto
    {
        $this->validateEmailNotExists($createUserInputDto->email);

        $authUser = $this->authenticator->getAuthUser();
        $typeToCreate = $this->determineToCreateUserType($createUserInputDto->type, $authUser);

        $this->validateUserTypePermission($typeToCreate, $authUser);

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

    private function validateEmailNotExists(string $email): void
    {
        if ($this->userRepository->getByEmail($email) !== null) {
            throw new EmailAlreadyExistsException;
        }
    }

    private function determineToCreateUserType(?string $type, ?object $authUser): UserType
    {
        $typeToCreate = $type ? UserType::from($type) : UserType::PARTICIPANT;

        if ($authUser === null) {
            return UserType::PARTICIPANT;
        }

        return $typeToCreate;
    }

    private function validateUserTypePermission(UserType $typeToCreate, ?object $authUser): void
    {
        if ($authUser === null) {
            return;
        }

        if ($typeToCreate === UserType::ORGANIZER && $authUser->canCreateOrganizerUser() === false) {
            throw new UnauthorizedException;
        }
    }
}
