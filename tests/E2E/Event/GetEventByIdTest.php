<?php

namespace Tests\E2E\Event;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetEventByIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_event_by_id_successfully()
    {
        $event = EventModel::factory()->create();

        $response = $this->getJson('/api/v1/events/'.$event->id);

        $response->assertStatus(200)->assertJsonStructure(['id']);
    }

    public function test_get_event_with_invalid_id()
    {
        $response = $this->getJson('/api/v1/events/xxx');

        $response->assertStatus(404);
    }
}
