<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\TalentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageApiTest extends TestCase
{
    use DatabaseTransactions;

    // ========================================
    // Featured Events
    // ========================================

    public function test_featured_events_returns_only_published_events(): void
    {
        $org = Organization::factory()->create(['status' => 'active']);

        // Published event (harus muncul)
        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'published',
            'start_date' => now()->addDays(3),
        ]);

        // Draft event (tidak boleh muncul)
        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'draft',
            'start_date' => now()->addDays(5),
        ]);

        Cache::flush();

        $response = $this->getJson('/api/homepage/featured-events');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $data = $response->json('data');
        foreach ($data as $event) {
            $this->assertArrayHasKey('name', $event);
            $this->assertArrayHasKey('posterUrl', $event);
            $this->assertArrayHasKey('startDate', $event);
            $this->assertArrayHasKey('organizer', $event);
        }
    }

    public function test_featured_events_excludes_past_events(): void
    {
        $org = Organization::factory()->create(['status' => 'active']);

        // Past event (tidak boleh muncul)
        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'published',
            'start_date' => now()->subDays(1),
        ]);

        // Future event (harus muncul)
        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'published',
            'start_date' => now()->addDays(7),
        ]);

        Cache::flush();

        $response = $this->getJson('/api/homepage/featured-events');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_featured_events_returns_max_3(): void
    {
        $org = Organization::factory()->create(['status' => 'active']);

        // Buat 5 event published
        for ($i = 1; $i <= 5; $i++) {
            Event::factory()->create([
                'organization_id' => $org->id,
                'status' => 'published',
                'start_date' => now()->addDays($i),
            ]);
        }

        Cache::flush();

        $response = $this->getJson('/api/homepage/featured-events');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(3, count($response->json('data')));
    }

    public function test_featured_events_ordered_by_nearest_date(): void
    {
        $org = Organization::factory()->create(['status' => 'active']);

        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'published',
            'title' => 'Event Jauh',
            'start_date' => now()->addDays(30),
        ]);

        Event::factory()->create([
            'organization_id' => $org->id,
            'status' => 'published',
            'title' => 'Event Dekat',
            'start_date' => now()->addDays(1),
        ]);

        Cache::flush();

        $response = $this->getJson('/api/homepage/featured-events');

        $data = $response->json('data');
        $this->assertEquals('Event Dekat', $data[0]['name']);
    }

    public function test_featured_events_empty_state_returns_200(): void
    {
        Cache::flush();

        $response = $this->getJson('/api/homepage/featured-events');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data', []);
    }

    // ========================================
    // Sub Organizations
    // ========================================

    public function test_sub_organizations_returns_only_active(): void
    {
        Organization::factory()->create(['status' => 'active', 'name' => 'Org Aktif']);
        Organization::factory()->create(['status' => 'inactive', 'name' => 'Org Nonaktif']);

        Cache::flush();

        $response = $this->getJson('/api/homepage/sub-organizations');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $data = $response->json('data');
        foreach ($data as $org) {
            $this->assertArrayHasKey('name', $org);
            $this->assertArrayHasKey('slug', $org);
            $this->assertArrayHasKey('logoUrl', $org);
        }
    }

    public function test_sub_organizations_empty_state_returns_200(): void
    {
        // Hapus semua org (soft delete)
        Organization::query()->forceDelete();
        Cache::flush();

        $response = $this->getJson('/api/homepage/sub-organizations');

        $response->assertStatus(200)
                 ->assertJsonPath('data', []);
    }

    // ========================================
    // Talent Highlights
    // ========================================

    public function test_talent_highlights_returns_only_with_bio(): void
    {
        $userWithBio = User::factory()->create(['role' => 'talent']);
        TalentProfile::factory()->create([
            'user_id' => $userWithBio->id,
            'bio' => 'Saya seorang musisi akustik.',
            'category' => 'Solo',
        ]);

        $userWithoutBio = User::factory()->create(['role' => 'talent']);
        TalentProfile::factory()->create([
            'user_id' => $userWithoutBio->id,
            'bio' => null,
        ]);

        Cache::flush();

        $response = $this->getJson('/api/homepage/talent-highlights');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('category', $data[0]);
        $this->assertArrayHasKey('bio', $data[0]);
        $this->assertArrayHasKey('portfolioUrl', $data[0]);
    }

    public function test_talent_highlights_empty_state_returns_200(): void
    {
        Cache::flush();

        $response = $this->getJson('/api/homepage/talent-highlights');

        $response->assertStatus(200)
                 ->assertJsonPath('data', []);
    }
}
