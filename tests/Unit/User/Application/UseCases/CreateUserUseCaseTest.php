<?php

namespace Tests\Unit\User\Application\UseCases;

use App\Modules\Shared\Application\Exceptions\UnauthorizedException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\User\Application\Exceptions\EmailAlreadyExistsException;
use App\Modules\User\Application\UseCases\CreateUserUseCase;
use App\Modules\User\Domain\Dtos\CreateUserInputDto;
use App\Modules\User\Domain\Dtos\UserOutputDto;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;
use Tests\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    private AuthenticatorAdapterInterface $authenticator;
    private UserRepositoryInterface $userRepository;
    private CreateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticator = $this->createMock(AuthenticatorAdapterInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->useCase = new CreateUserUseCase(
            $this->authenticator,
            $this->userRepository
        );
    }

    private function createOrganizerUser(): User
    {
        return new User(
            id: 'organizer_123',
            name: 'João Organizador',
            email: new Email('joao@barros.com'),
            type: UserType::ORGANIZER,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    private function createParticipantUser(): User
    {
        return new User(
            id: 'participant_123',
            name: 'João Participante',
            email: new Email('joao@barros.com'),
            type: UserType::PARTICIPANT,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );
    }

    public function test_create_user_successfully()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Pedro Barros',
            email: 'pedro@barros.com',
            type: null,
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('pedro@barros.com')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn(null);

        $createdUser = new User(
            id: 'user_123',
            name: 'Pedro Barros',
            email: new Email('pedro@barros.com'),
            type: UserType::PARTICIPANT,
            password: new Password('password123'),
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdUser);

        $result = $this->useCase->execute($inputDto);

        $this->assertInstanceOf(UserOutputDto::class, $result);
        $this->assertEquals('user_123', $result->id);
        $this->assertEquals('Pedro Barros', $result->name);
        $this->assertEquals('pedro@barros.com', $result->email);
        $this->assertEquals('participant', $result->type);
    }

    public function test_create_user_with_invalid_email_throws_exception()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Pedro Silva',
            email: 'pedrosilva.com',
            type: null,
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('pedrosilva.com')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn(null);

        $this->userRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(ValidationException::class);

        $this->useCase->execute($inputDto);
    }

    public function test_create_user_with_short_password_throws_exception()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Pedro Silva',
            email: 'pedro@example.com',
            type: null,
            password: 'pass'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('pedro@example.com')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn(null);

        $this->userRepository
            ->expects($this->never())
            ->method('create');

        try {
            $this->useCase->execute($inputDto);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('password', $context);
            $this->assertEquals('A senha deve ter no mínimo 8 caracteres.', $context['password']);
        }
    }

    public function test_create_user_with_duplicate_email_throws_exception()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Pedro Silva',
            email: 'existing@example.com',
            type: null,
            password: 'password123'
        );

        $existingUser = new User(
            id: 'existing_123',
            name: 'Usuário Existente',
            email: new Email('existing@example.com'),
            type: UserType::PARTICIPANT,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        $this->authenticator
            ->expects($this->never())
            ->method('getAuthUser');

        $this->userRepository
            ->expects($this->never())
            ->method('create');

        try {
            $this->useCase->execute($inputDto);
        } catch (EmailAlreadyExistsException $e) {
            $this->assertEquals('Os dados fornecidos são inválidos.', $e->getMessage());
            $context = $e->getContext();
            $this->assertArrayHasKey('email', $context);
            $this->assertEquals('O email informado já está sendo usado.', $context['email']);
        }
    }

    public function test_non_organizer_cannot_create_organizer_user()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Novo Organizador',
            email: 'novo.organizador@example.com',
            type: 'organizer',
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('novo.organizador@example.com')
            ->willReturn(null);

        $participantUser = $this->createParticipantUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($participantUser);

        $this->userRepository
            ->expects($this->never())
            ->method('create');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Operação não autorizada.');

        $this->useCase->execute($inputDto);
    }

    public function test_organizer_can_create_organizer_user()
    {
        $inputDto = new CreateUserInputDto(
            name: 'Novo Organizador',
            email: 'novo.organizador@example.com',
            type: 'organizer',
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('novo.organizador@example.com')
            ->willReturn(null);

        $organizerUser = $this->createOrganizerUser();

        $this->authenticator
            ->expects($this->once())
            ->method('getAuthUser')
            ->willReturn($organizerUser);

        $createdUser = new User(
            id: 'new_organizer_123',
            name: 'Novo Organizador',
            email: new Email('novo.organizador@example.com'),
            type: UserType::ORGANIZER,
            password: new Password('password123'),
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-01 10:00:00'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdUser);

        $result = $this->useCase->execute($inputDto);

        $this->assertInstanceOf(UserOutputDto::class, $result);
        $this->assertEquals('new_organizer_123', $result->id);
        $this->assertEquals('Novo Organizador', $result->name);
        $this->assertEquals('novo.organizador@example.com', $result->email);
        $this->assertEquals('organizer', $result->type);
    }
}

