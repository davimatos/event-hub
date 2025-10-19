<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\Shared\Application\Exceptions\UnauthorizedException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\User\Application\Exceptions\EmailAlreadyExistsException;
use App\Modules\User\Domain\Dtos\CreateUserInputDto;
use App\Modules\User\Domain\Dtos\UserOutputDto;
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
