<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Verlofaanvraag;
use App\Models\Verloftype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

class VerlofTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
       public function check_of_pagina_laad()
    {

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verlof/beoordeling');

        $response->assertStatus(200);

        $response->assertInertia(
            fn(\Inertia\Testing\AssertableInertia $page) =>
            $page->component('Verlof/aanvraag')
        );
    }
    /** @test */
    public function verlof_test_route_returns_verloftypes()
    {
        // Create verloftypes manually
        Verloftype::create(['naam' => 'Vakantie', 'betaald' => true]);
        Verloftype::create(['naam' => 'Ziekteverlof', 'betaald' => true]);
        Verloftype::create(['naam' => 'Onbetaald verlof', 'betaald' => false]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verlof/aanvragen');

        $response->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) =>
                $page->has('types', 3)
            );
    }

    /** @test */
    public function store_creates_new_verlofaanvraag()
    {
        $user = User::factory()->create();

        $verloftype = Verloftype::create([
            'naam' => 'Vakantie',
            'betaald' => true,
        ]);

        $payload = [
            'verlof_type_id' => $verloftype->id,
            'start_datum' => '2025-10-10',
            'eind_datum' => '2025-10-12',
            'reden' => 'Vakantie',
        ];

        $response = $this->actingAs($user)
            ->post('/verlof/aanvragen', $payload);

        // Verwacht redirect 
        $response->assertStatus(302);

        // Controleer database — gebruik juiste tabelnaam
        $this->assertDatabaseHas('verlofaanvragen', [
            'medewerker_id' => $user->id,
            'verlof_type_id' => $verloftype->id,
            'reden' => 'Vakantie',
            'status' => 'pending',
        ]);
    }
    
 

}
