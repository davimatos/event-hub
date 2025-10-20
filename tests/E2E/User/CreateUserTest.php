<?php

namespace Tests\E2E\User;

use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_user_successfully()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'type' => 'participant',
            ]);
    }

    public function test_admin_can_create_organizer_user()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'type' => 'organizer',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'type' => 'organizer',
            ]);
    }

    public function test_participant_can_create_participant_user()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
            'email' => 'participant@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'type' => 'participant',
            ]);
    }

    public function test_participant_cannot_create_organizer_user()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
            'email' => 'participant@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'type' => 'organizer',
            ]);

        $response->assertStatus(403);
    }

    public function test_create_user_without_authentication()
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'organizer',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_user_with_existing_email()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(201);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(409)->assertJsonStructure(['errors' => ['email']]);
    }
}
