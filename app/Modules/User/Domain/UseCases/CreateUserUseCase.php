<?php

namespace App\Modules\User\Domain\UseCases;

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
    function __construct(
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

        $user = new User(
            id: null,
            name: $createUserInputDto->name,
            email: new Email($createUserInputDto->email),
            type: UserType::PARTICIPANT,
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
