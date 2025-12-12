<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_update_profile_information()
    {
        $user = User::factory()->create([
            'voornaam' => 'Jan',
            'email' => 'old@example.com',
        ]);

        $payload = [
            'voornaam' => 'Peter',
            'email' => 'new@example.com',
        ];

        $response = $this
            ->actingAs($user)
            ->putJson('/api/user', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profiel succesvol bijgewerkt.',
            ]);

        $user->refresh();

        $this->assertSame('Peter', $user->voornaam);
        $this->assertSame('new@example.com', $user->email);
    }

    /** @test */
    public function user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
        ]);

        $payload = [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        $response = $this
            ->actingAs($user)
            ->putJson('/api/user/password', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Wachtwoord succesvol gewijzigd.',
            ]);

        $this->assertTrue(
            Hash::check('newpassword', $user->fresh()->password)
        );
    }
}
