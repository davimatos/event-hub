<?php

namespace Tests\E2E\Order;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all_orders_successfully()
    {
        $participant = UserModel::factory()->create([
            'type' => 'participant',
        ]);

        $organizer = UserModel::factory()->create([
            'type' => 'organizer',
        ]);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
        ]);

        OrderModel::factory()->count(3)->create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
        ]);

        $response = $this->actingAs($participant)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'event_id',
                        'participant_id',
                        'quantity',
                        'ticket_price',
                        'discount',
                        'total_amount',
                        'status',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'items');
    }

    public function test_list_orders_without_authentication()
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(401);
    }

    public function test_participant_sees_only_own_orders()
    {
        $participant1 = UserModel::factory()->create(['type' => 'participant']);
        $participant2 = UserModel::factory()->create(['type' => 'participant']);

        $organizer = UserModel::factory()->create(['type' => 'organizer']);

        $event = EventModel::factory()->create([
            'organizer_id' => $organizer->id,
        ]);

        OrderModel::factory()->count(2)->create([
            'event_id' => $event->id,
            'participant_id' => $participant1->id,
        ]);

        OrderModel::factory()->count(3)->create([
            'event_id' => $event->id,
            'participant_id' => $participant2->id,
        ]);

        $response = $this->actingAs($participant1)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'items');

        $items = $response->json('items');

        foreach ($items as $item) {
            $this->assertEquals($participant1->id, $item['participant_id']);
        }
    }

    public function test_organizer_sees_orders_for_their_events()
    {
        $organizer1 = UserModel::factory()->create(['type' => 'organizer']);
        $organizer2 = UserModel::factory()->create(['type' => 'organizer']);

        $participant = UserModel::factory()->create(['type' => 'participant']);

        $event1 = EventModel::factory()->create(['organizer_id' => $organizer1->id]);
        $event2 = EventModel::factory()->create(['organizer_id' => $organizer2->id]);

        OrderModel::factory()->count(3)->create([
            'event_id' => $event1->id,
            'participant_id' => $participant->id,
        ]);

        OrderModel::factory()->count(2)->create([
            'event_id' => $event2->id,
            'participant_id' => $participant->id,
        ]);

        $response = $this->actingAs($organizer1)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'items');

        $items = $response->json('items');
        foreach ($items as $item) {
            $this->assertEquals($event1->id, $item['event_id']);
        }
    }
}
