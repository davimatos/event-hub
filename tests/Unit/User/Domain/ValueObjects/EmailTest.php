<?php

namespace Tests\Unit\User\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function test_create_email_successfully()
    {
        $email = new Email("joao@barros.com");

        $this->assertInstanceOf(Email::class, $email);
    }

    public function test_email_with_invalid_format_throws_exception()
    {
        try {
            new Email("joaobarros.com");
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('email', $context);
            $this->assertEquals('Endereço de email inválido.', $context['email']);
        }
    }

    public function test_email_is_trimmed()
    {
        $emailString = ' joao@barros.com';

        $email = new Email($emailString);

        $this->assertEquals('joao@barros.com', (string) $email);
    }

    public function test_email_to_string_returns_address()
    {
        $emailString = 'joao@barros.com';

        $email = new Email($emailString);

        $this->assertEquals($emailString, (string) $email);
    }
}

