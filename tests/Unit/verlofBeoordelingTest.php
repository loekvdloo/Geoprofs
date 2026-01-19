<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class VerlofBeoordelingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verlofaanvragen_pagina_kan_laden()
    {
        // Maak een gebruiker
        $user = User::factory()->create();

        // Bezoek de pagina
        $response = $this->actingAs($user)->get('/verlof/beoordeling');

        $response->assertStatus(200);

        // Alleen check dat de juiste Inertia component wordt geladen
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Verlof/beoordeling')
        );
    }
}
