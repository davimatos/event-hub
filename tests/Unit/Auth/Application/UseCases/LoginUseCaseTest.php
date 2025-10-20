<?php

namespace Tests\Unit\Auth\Application\UseCases;

use App\Modules\Auth\Application\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Application\UseCases\LoginUseCase;
use App\Modules\Auth\Domain\Dtos\LoginInputDto;
use App\Modules\Auth\Domain\Dtos\LoginOutputDto;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Repositories\ConfigParamsRepositoryInterface;
use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Email;
use App\Modules\User\Domain\ValueObjects\Password;
use Tests\TestCase;

class LoginUseCaseTest extends TestCase
{
    private AuthenticatorAdapterInterface $authenticator;

    private ConfigParamsRepositoryInterface $configParams;

    private UserRepositoryInterface $userRepository;

    private LoginUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticator = $this->createMock(AuthenticatorAdapterInterface::class);
        $this->configParams = $this->createMock(ConfigParamsRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->useCase = new LoginUseCase(
            $this->authenticator,
            $this->configParams,
            $this->userRepository
        );
    }

    private function createValidUser(): User
    {
        return new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::PARTICIPANT,
            password: new Password('password123')
        );
    }

    public function test_login_with_valid_credentials_returns_token()
    {
        $inputDto = new LoginInputDto(
            email: 'joao@barros.com',
            password: 'password123'
        );

        $user = $this->createValidUser();

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('joao@barros.com')
            ->willReturn($user);

        $this->authenticator
            ->expects($this->once())
            ->method('checkCredentials')
            ->with('joao@barros.com', 'password123')
            ->willReturn(true);

        $this->authenticator
            ->expects($this->once())
            ->method('generateToken')
            ->willReturn('generated_token_12345');

        $this->configParams
            ->expects($this->once())
            ->method('authTokenLifetimeInMinutes')
            ->willReturn(60);

        $result = $this->useCase->execute($inputDto);

        $this->assertInstanceOf(LoginOutputDto::class, $result);
        $this->assertEquals('generated_token_12345', $result->token);
        $this->assertEquals('Bearer', $result->tokenType);
    }

    public function test_login_with_invalid_email_throws_exception()
    {
        $inputDto = new LoginInputDto(
            email: 'jj@barros.com',
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('jj@barros.com')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->never())
            ->method('checkCredentials');

        $this->authenticator
            ->expects($this->never())
            ->method('generateToken');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Credenciais inválidas.');

        $this->useCase->execute($inputDto);
    }

    public function test_login_with_invalid_password_throws_exception()
    {
        $inputDto = new LoginInputDto(
            email: 'joao@barros.com',
            password: 'wordpass'
        );

        $user = $this->createValidUser();

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('joao@barros.com')
            ->willReturn($user);

        $this->authenticator
            ->expects($this->once())
            ->method('checkCredentials')
            ->with('joao@barros.com', 'wordpass')
            ->willReturn(false);

        $this->authenticator
            ->expects($this->never())
            ->method('generateToken');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Credenciais inválidas.');

        $this->useCase->execute($inputDto);
    }

    public function test_login_with_non_existent_user_throws_exception()
    {
        $inputDto = new LoginInputDto(
            email: 'outro@example.com',
            password: 'password123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('outro@example.com')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->never())
            ->method('checkCredentials');

        $this->authenticator
            ->expects($this->never())
            ->method('generateToken');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Credenciais inválidas.');

        $this->useCase->execute($inputDto);
    }
}
