<?php

namespace Tests\Unit\User\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\User\Domain\ValueObjects\Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    public function test_create_password_successfully()
    {
        $password = new Password('password123');

        $this->assertInstanceOf(Password::class, $password);
    }

    public function test_password_must_have_minimum_8_characters()
    {
        try {
            new Password('short');
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('password', $context);
            $this->assertEquals('A senha deve ter no mínimo 8 caracteres.', $context['password']);
        }
    }

    public function test_password_to_string_returns_password()
    {
        $passwordString = 'password123';

        $password = new Password($passwordString);

        $this->assertEquals($passwordString, (string) $password);
    }
}
