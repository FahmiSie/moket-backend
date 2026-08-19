<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class GlobalRoleMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test super_admin can access admin-only route.
     */
    public function test_super_admin_can_access_admin_route(): void
    {
        $superAdmin = User::factory()->make(['role' => 'super_admin']);
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/admin-only');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Welcome Super Admin!']);
    }

    /**
     * Test other global roles are forbidden.
     */
    public function test_other_roles_are_forbidden(): void
    {
        $roles = ['user', 'talent', 'mentor'];

        foreach ($roles as $role) {
            $user = User::factory()->make(['role' => $role]);
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/admin-only');

            $response->assertStatus(403);
        }
    }
}
