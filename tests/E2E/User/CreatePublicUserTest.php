<?php

namespace Tests\E2E\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePublicUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_public_user_successfully()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_create_public_user_with_existing_email()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);

        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(409);
    }

    public function test_create_public_user_with_invalid_email()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joaobarros.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_create_public_user_with_missing_required_fields()
    {
        $response = $this->postJson('/api/v1/public/users', []);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['email', 'name', 'password']]);
    }

    public function test_create_public_user_without_password_confirmation()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'password',
            'password_confirmation' => 'password213',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_create_public_user_with_password_less_than_8_chars()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_create_public_user_participant_type_only()
    {
        $response = $this->postJson('/api/v1/public/users', [
            'name' => 'João Barros',
            'email' => 'joao@barros.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'type' => 'organizer',
        ]);

        $response->assertStatus(201)->assertJson([
            'type' => 'participant',
        ]);
    }
}

