<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Event;
use Laravel\Sanctum\Sanctum;

class ContextualPolicyTest extends TestCase
{
    use DatabaseTransactions;

    // ================================================================
    // Test 2 — Correct organization: Scanner di Org A scan Event Org A → 200
    // ================================================================
    public function test_2_correct_organization_scanner_can_check_in(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $orgA = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $orgA->id,
            'user_id' => $user->id,
            'role' => 'scanner',
            'status' => 'active',
        ]);
        $eventA = Event::factory()->create(['organization_id' => $orgA->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/test/events/{$eventA->id}/check-in");
        $response->assertStatus(200);
    }

    // ================================================================
    // Test 3 — Wrong organization: Scanner di Org A scan Event Org B → 403
    // ================================================================
    public function test_3_wrong_organization_scanner_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        OrganizationMember::factory()->create([
            'organization_id' => $orgA->id,
            'user_id' => $user->id,
            'role' => 'scanner',
            'status' => 'active',
        ]);

        // Event milik Org B, bukan Org A
        $eventB = Event::factory()->create(['organization_id' => $orgB->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/test/events/{$eventB->id}/check-in");
        $response->assertStatus(403);
    }

    // ================================================================
    // Test 4 — Admin bisa manage organization & event
    // ================================================================
    public function test_4_admin_can_manage_organization(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $org = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'status' => 'active',
        ]);
        $event = Event::factory()->create(['organization_id' => $org->id]);

        Sanctum::actingAs($user);

        // Admin bisa update org
        $this->putJson("/api/test/organizations/{$org->id}")->assertStatus(200);
        // Admin bisa manage members
        $this->postJson("/api/test/organizations/{$org->id}/members")->assertStatus(200);
        // Admin bisa create event
        $this->postJson("/api/test/organizations/{$org->id}/events")->assertStatus(200);
        // Admin bisa update event
        $this->putJson("/api/test/events/{$event->id}")->assertStatus(200);
        // Admin bisa delete event
        $this->deleteJson("/api/test/events/{$event->id}")->assertStatus(200);
    }

    // ================================================================
    // Test 5 — Committee hanya bisa operasi committee, bukan admin-only
    // ================================================================
    public function test_5_committee_cannot_do_admin_operations(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $org = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'committee',
            'status' => 'active',
        ]);
        $event = Event::factory()->create(['organization_id' => $org->id]);

        Sanctum::actingAs($user);

        // Committee BISA view org
        $this->getJson("/api/test/organizations/{$org->id}")->assertStatus(200);
        // Committee BISA create event
        $this->postJson("/api/test/organizations/{$org->id}/events")->assertStatus(200);
        // Committee BISA update event
        $this->putJson("/api/test/events/{$event->id}")->assertStatus(200);
        // Committee TIDAK BISA update org (admin-only)
        $this->putJson("/api/test/organizations/{$org->id}")->assertStatus(403);
        // Committee TIDAK BISA manage members (admin-only)
        $this->postJson("/api/test/organizations/{$org->id}/members")->assertStatus(403);
        // Committee TIDAK BISA delete event (admin-only)
        $this->deleteJson("/api/test/events/{$event->id}")->assertStatus(403);
    }

    // ================================================================
    // Test 6 — Super Admin bypass contextual restrictions
    // ================================================================
    public function test_6_super_admin_bypasses_all_policies(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $org = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        // Super admin TIDAK terdaftar sebagai anggota org manapun
        Sanctum::actingAs($superAdmin);

        $this->getJson("/api/test/organizations/{$org->id}")->assertStatus(200);
        $this->putJson("/api/test/organizations/{$org->id}")->assertStatus(200);
        $this->postJson("/api/test/organizations/{$org->id}/members")->assertStatus(200);
        $this->postJson("/api/test/organizations/{$org->id}/events")->assertStatus(200);
        $this->putJson("/api/test/events/{$event->id}")->assertStatus(200);
        $this->deleteJson("/api/test/events/{$event->id}")->assertStatus(200);
        $this->postJson("/api/test/events/{$event->id}/check-in")->assertStatus(200);
        $this->postJson("/api/test/events/{$event->id}/tickets")->assertStatus(200);
    }

    // ================================================================
    // Test 7 — Invited but not yet active → 403
    // ================================================================
    public function test_7_invited_membership_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $org = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'scanner',
            'status' => 'invited', // <-- belum aktif!
        ]);
        $event = Event::factory()->create(['organization_id' => $org->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/test/events/{$event->id}/check-in");
        $response->assertStatus(403);
    }

    // ================================================================
    // Test 8 — Inactive membership → 403
    // ================================================================
    public function test_8_inactive_membership_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $org = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'scanner',
            'status' => 'inactive', // <-- dinonaktifkan!
        ]);
        $event = Event::factory()->create(['organization_id' => $org->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/test/events/{$event->id}/check-in");
        $response->assertStatus(403);
    }

    // ================================================================
    // Test 9 — Active membership → 200
    // ================================================================
    public function test_9_active_membership_is_authorized(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $org = Organization::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'scanner',
            'status' => 'active', // <-- aktif!
        ]);
        $event = Event::factory()->create(['organization_id' => $org->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/test/events/{$event->id}/check-in");
        $response->assertStatus(200);
    }
}
