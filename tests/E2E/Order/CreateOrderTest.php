<?php

namespace Tests\E2E\Order;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_successfully()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
            'ticket_price' => 100.00,
            'capacity' => 100,
            'remaining_tickets' => 100,
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => $event->id,
                'quantity' => 2,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'quantity' => 2,
                'ticket_price' => 100.00,
                'discount' => 0.00,
                'total_amount' => 200.00,
                'status' => 'confirmed',
            ]);

        $this->assertDatabaseHas('orders', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'quantity' => 2,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_create_order_without_authentication()
    {
        $response = $this->postJson('/api/v1/buy-ticket', [
                'event_id' => '1',
                'quantity' => 2,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(401);
    }

    public function test_create_order_with_invalid_event_id()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => '01JAXXXXXXXXXXXXXXXXXXX',
                'quantity' => 2,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(404);
    }

    public function test_create_order_exceeding_event_capacity()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
            'ticket_price' => 100.00,
            'capacity' => 10,
            'remaining_tickets' => 5,
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => $event->id,
                'quantity' => 10,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(403)->assertJsonStructure(['errors' => ['quantity']]);
    }

    public function test_create_order_exceeding_max_tickets_per_order()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
            'ticket_price' => 100.00,
            'capacity' => 100,
            'remaining_tickets' => 100,
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => $event->id,
                'quantity' => 50,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(403)->assertJsonStructure(['errors' => ['quantity']]);
    }

    public function test_create_order_exceeding_max_tickets_per_participant()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
            'ticket_price' => 100.00,
            'capacity' => 100,
            'remaining_tickets' => 100,
        ]);

        OrderModel::factory()->create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'quantity' => 15,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => $event->id,
                'quantity' => 3,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $response->assertStatus(403)->assertJsonStructure(['errors' => ['quantity']]);
    }

    public function test_create_order_decrements_event_remaining_tickets()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
            'ticket_price' => 100.00,
            'capacity' => 100,
            'remaining_tickets' => 100,
        ]);

        $initialRemainingTickets = $event->remaining_tickets;

        $this->actingAs($participant)
            ->postJson('/api/v1/buy-ticket', [
                'event_id' => $event->id,
                'quantity' => 5,
                'card_number' => '4111111111111111',
                'card_holder_name' => 'João Barros',
                'card_expiration_date' => '12/26',
                'card_cvv' => '123',
            ]);

        $event->refresh();

        $this->assertEquals($initialRemainingTickets - 5, $event->remaining_tickets);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'remaining_tickets' => 95,
        ]);
    }
}
