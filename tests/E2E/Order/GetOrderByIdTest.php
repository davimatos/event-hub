<?php

namespace Tests\E2E\Order;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\TicketModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOrderByIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_order_by_id_successfully()
    {
        $participant = UserModel::factory()->create(['type' => 'participant']);
        $organizer = UserModel::factory()->create(['type' => 'organizer']);

        $event = EventModel::factory()->create(['organizer_id' => $organizer->id]);

        $order = OrderModel::factory()->create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'quantity' => 2,
        ]);

        TicketModel::factory()->count(2)->create([
            'order_id' => $order->id,
            'event_id' => $event->id,
            'participant_id' => $participant->id,
        ]);

        $response = $this->actingAs($participant)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'quantity' => 2,
            ]);
    }

    public function test_get_order_without_authentication()
    {
        $participant = UserModel::factory()->create(['type' => 'participant']);
        $organizer = UserModel::factory()->create(['type' => 'organizer']);

        $event = EventModel::factory()->create(['organizer_id' => $organizer->id]);

        $order = OrderModel::factory()->create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(401);
    }

    public function test_participant_cannot_access_other_user_order()
    {
        $participant1 = UserModel::factory()->create(['type' => 'participant']);
        $participant2 = UserModel::factory()->create(['type' => 'participant']);
        $organizer = UserModel::factory()->create(['type' => 'organizer']);

        $event = EventModel::factory()->create(['organizer_id' => $organizer->id]);

        $order = OrderModel::factory()->create([
            'event_id' => $event->id,
            'participant_id' => $participant1->id,
        ]);

        $response = $this->actingAs($participant2)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_organizer_cannot_access_order_from_other_event()
    {
        $participant = UserModel::factory()->create(['type' => 'participant']);
        $organizer1 = UserModel::factory()->create(['type' => 'organizer']);
        $organizer2 = UserModel::factory()->create(['type' => 'organizer']);

        $event1 = EventModel::factory()->create(['organizer_id' => $organizer1->id]);
        $event2 = EventModel::factory()->create(['organizer_id' => $organizer2->id]);

        $order = OrderModel::factory()->create([
            'event_id' => $event1->id,
            'participant_id' => $participant->id,
        ]);

        $response = $this->actingAs($organizer2)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_get_order_with_invalid_id()
    {
        $participant = UserModel::factory()->create(['type' => 'participant']);

        $response = $this->actingAs($participant)->getJson('/api/v1/orders/invalid-id-123');

        $response->assertStatus(404);
    }
}
