<?php

namespace Tests\E2E\Event;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all_events_successfully()
    {
        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)->assertJsonStructure(['items']);
    }
}

