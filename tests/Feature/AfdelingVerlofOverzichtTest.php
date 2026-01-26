<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfdelingVerlofOverzichtTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function api_endpoint_returns_200()
    {
        // Stap 1: Rol en afdeling aanmaken
        $role = Role::create(['role_naam' => 'Medewerker']);
        $afdeling = Afdeling::create(['afdeling_naam' => 'ICT']);

        // Stap 2: Gebruiker aanmaken
        $user = User::factory()->create([
            'role_id' => $role->id,
            'afdeling_id' => $afdeling->id,
        ]);

        // Stap 4: De API endpoint testen
        $response = $this->actingAs($user)->getJson('/api/verlof/afdeling/overzicht');

        $response->assertStatus(200);
    }
}