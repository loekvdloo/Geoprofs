<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerlofSaldoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function werknemer_kan_verlofsaldo_opvragen()
    {
        // Arrange: gebruiker
        $user = User::factory()->create([
            'verlofsaldo' => 15, 
        ]);

        // Act: saldo ophalen
        $response = $this->actingAs($user)
            ->getJson('/api/verlof/saldo');

        // Assert: juiste response
        $response->assertStatus(200)
            ->assertJson([
                'verlofsaldo' => 15,
            ]);
    }
}
