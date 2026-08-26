<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventDiscoveryApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_event_discovery_without_filters_returns_paginated_published_events(): void
    {
        $org = Organization::factory()->create();
        
        // Buat 15 event (limit pagination adalah 12)
        Event::factory()->count(15)->create([
            'organization_id' => $org->id,
            'status' => 'published',
        ]);
        
        // 1 Draft event yang tidak boleh muncul
        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
                 
        $data = $response->json('data');
        $meta = $response->json('meta');
        
        $this->assertCount(12, $data); // Default per page
        $this->assertEquals(15, $meta['total']); // Total cuma 15 yang published
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('posterUrl', $data[0]);
    }

    public function test_event_discovery_can_filter_by_search_keyword(): void
    {
        $org = Organization::factory()->create();
        Event::factory()->create(['organization_id' => $org->id, 'status' => 'published', 'title' => 'Konser Musik Kemerdekaan']);
        Event::factory()->create(['organization_id' => $org->id, 'status' => 'published', 'title' => 'Seminar Teknologi']);

        $response = $this->getJson('/api/events?q=musik');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('Konser Musik Kemerdekaan', $data[0]['name']);
    }

    public function test_event_discovery_can_filter_by_category_and_scope(): void
    {
        $org = Organization::factory()->create();
        
        Event::factory()->create(['organization_id' => $org->id, 'status' => 'published', 'category' => 'music', 'scope' => 'internal']);
        Event::factory()->create(['organization_id' => $org->id, 'status' => 'published', 'category' => 'music', 'scope' => 'external']);
        Event::factory()->create(['organization_id' => $org->id, 'status' => 'published', 'category' => 'tech', 'scope' => 'external']);

        $response = $this->getJson('/api/events?category=music&scope=external');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('music', $data[0]['category']);
        $this->assertEquals('external', $data[0]['scope']);
    }
    
    public function test_event_discovery_empty_state(): void
    {
        $response = $this->getJson('/api/events?q=impossible_search_query_that_yields_nothing');
        
        $response->assertStatus(200)
                 ->assertJsonPath('data', []);
    }
}
