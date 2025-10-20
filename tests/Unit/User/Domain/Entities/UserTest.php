<?php

namespace Tests\Unit\User\Domain\Entities;

use App\Modules\User\Domain\Entities\User;
use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Domain\ValueObjects\Email;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_create_user_successfully()
    {
        $user = new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::ORGANIZER,
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('user_123', $user->id);
        $this->assertEquals('João Barros', $user->name);
        $this->assertEquals(new Email('joao@barros.com'), $user->email);
        $this->assertEquals(UserType::ORGANIZER, $user->type);
    }

    public function test_is_organizer_returns_true_for_organizer_type()
    {
        $user = new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::ORGANIZER,
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->isOrganizer());
    }

    public function test_is_organizer_returns_false_for_participant_type()
    {
        $user = new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::PARTICIPANT,
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertFalse($user->isOrganizer());
    }

    public function test_can_create_organizer_user_returns_true_for_organizer()
    {
        $user = new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::ORGANIZER,
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->canCreateOrganizerUser());
    }

    public function test_can_create_organizer_user_returns_false_for_participant()
    {
        $user = new User(
            id: 'user_123',
            name: 'João Barros',
            email: new Email('joao@barros.com'),
            type: UserType::PARTICIPANT,
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertFalse($user->canCreateOrganizerUser());
    }
}

