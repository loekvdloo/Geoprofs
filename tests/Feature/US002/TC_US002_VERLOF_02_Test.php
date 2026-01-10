<?php

namespace Tests\Feature\US002;

use App\Models\Role;
use App\Models\User;
use App\Models\Verloftype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TC_US002_VERLOF_02_Test extends TestCase
{
    use RefreshDatabase;

    public function test_tc_us002_verlof_02_manager_ziet_reden_in_overzicht(): void
    {
        // Arrange
        $employeeRole = Role::firstOrCreate(
            ['role_naam' => 'Medewerker'],
            ['role_naam' => 'Medewerker']
        );

        // Manager moet volgens jou dezelfde role_id hebben als medewerker om aanvragen te zien
        $manager = User::factory()->create([
            'email'   => 'manager@geoprofs.nl',
            'role_id' => $employeeRole->role_id,
        ]);

        $vakantieType = Verloftype::firstOrCreate(
            ['naam' => 'Vakantie'],
            ['naam' => 'Vakantie', 'betaald' => true]
        );

        $reden = 'Vakantie met familie naar Spanje';

        // We maken de aanvraag "van de manager zelf", want de endpoint heet letterlijk: mijn-aanvragen
        \DB::table('verlofaanvraag')->insert([
            'user_id'        => $manager->user_id,
            'verlof_type_id' => $vakantieType->verlof_type_id,
            'start_datum'    => '2025-12-10',
            'eind_datum'     => '2025-12-12',
            'reden'          => $reden,
            'status'         => 'pending',
            'aanvraag_datum' => now(),
        ]);

        // Act: echte endpoint uit Network tab
        $response = $this->actingAs($manager)->getJson('/api/verlof/mijn-aanvragen');

        // Assert
        $response->assertSuccessful();
        $response->assertJsonFragment(['reden' => $reden]);
    }
}
