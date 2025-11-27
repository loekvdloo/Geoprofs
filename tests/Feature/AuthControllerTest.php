<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_register()
    {
        $response = $this->postJson('/api/register', [
            'voornaam' => 'Loek',
            'achternaam' => 'Test',
            'email' => 'loek@test.nl',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'loek@test.nl',
        ]);
    }
    public function test_login()
    {
        $user = User::factory()->create([
            'email' => 'loek@test.nl',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'loek@test.nl',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }
    public function test_get_authenticated_user()
    {
        $user = User::factory()->create();


        $token = $user->createToken('auth_token')->plainTextToken;


        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/user');


        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->user_id,
                'email' => $user->email,
                'voornaam' => $user->voornaam,
                'achternaam' => $user->achternaam,
            ]);
    }
    public function test_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        $this->assertCount(0, $user->tokens);
    }

}
