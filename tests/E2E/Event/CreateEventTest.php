<?php

namespace Tests\E2E\Event;

use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_create_event_successfully()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->addDays(10)->toDateString(),
                'capacity' => 100,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'organizer' => [
                    'id' => $organizer->id,
                ],
                'title' => 'Novo evento',
                'capacity' => 100,
                'remaining_tickets' => 100,
                'ticket_price' => 50.00,
            ]);
    }

    public function test_create_event_without_authentication()
    {
        $response = $this->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->addDays(10)->toDateString(),
                'capacity' => 100,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(401);
    }

    public function test_participant_cannot_create_event()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
            'email' => 'participant@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->addDays(10)->toDateString(),
                'capacity' => 100,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(403);
    }

    public function test_create_event_with_past_date()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->subDays(5)->toDateString(),
                'capacity' => 100,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['date']]);
    }

    public function test_create_event_with_invalid_capacity()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->subDays(5)->toDateString(),
                'capacity' => -100,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['capacity']]);
    }

    public function test_create_event_with_negative_ticket_price()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->subDays(5)->toDateString(),
                'capacity' => 100,
                'ticket_price' => -50.00,
            ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['*']]);
    }

    public function test_create_event_with_missing_required_fields()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', []);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['title', 'description', 'date', 'capacity', 'ticket_price']]);
    }

    public function test_create_event_with_zero_capacity()
    {
        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
            'email' => 'participant@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Novo evento',
                'description' => 'Esse é um novo evento',
                'date' => now()->subDays(5)->toDateString(),
                'capacity' => 0,
                'ticket_price' => 50.00,
            ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['capacity']]);
    }
}

