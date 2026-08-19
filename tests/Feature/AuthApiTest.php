<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\TalentProfile;
use Laravel\Sanctum\Sanctum;

class AuthApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'johndoe' . time() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'phone' => '08123456789',
            'school_origin' => 'SMK Telkom',
            'class_batch' => 'Class of 2024',
            'category' => 'internal'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role',
                         ],
                         'token'
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'role' => 'user'
        ]);
        
        $this->assertDatabaseHas('user_profiles', [
            'full_name' => 'John Doe',
            'phone' => '08123456789',
        ]);
    }
    
    /**
     * Test successful talent registration.
     */
    public function test_talent_can_register_successfully(): void
    {
        $payload = [
            'name' => 'Talent Star',
            'email' => 'talent' . time() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'talent',
            'phone' => '08987654321',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('talent_profiles', []);
    }

    /**
     * Test validation error on registration (missing required fields).
     */
    public function test_registration_requires_mandatory_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors'
                 ]);
    }

    /**
     * Test user login successfully.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login_test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login_test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => ['id', 'email'],
                         'token'
                     ]
                 ]);
    }

    /**
     * Test getting current user profile via /api/me
     */
    public function test_get_current_user_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'user' => [
                             'id' => $user->id,
                             'email' => $user->email,
                         ]
                     ]
                 ]);
    }

    /**
     * Test logout successfully.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
    }
}
