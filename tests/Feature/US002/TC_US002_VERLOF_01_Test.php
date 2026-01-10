<?php

namespace Tests\Feature\US002;

use App\Models\Role;
use App\Models\User;
use App\Models\Verloftype;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VerloftypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TC_US002_VERLOF_01_Test extends TestCase
{
    use RefreshDatabase;

    public function test_tc_us002_verlof_01_reden_wordt_opgeslagen_en_exact_teruggegeven_in_mijn_aanvragen(): void
    {
        // Seed basisdata
        $this->seed(RoleSeeder::class);
        $this->seed(VerloftypeSeeder::class);

        $employeeRole = Role::where('role_naam', 'Medewerker')->firstOrFail();

        // SQLite kent geen ILIKE -> gebruik lower() + like
        $vakantieType = Verloftype::whereRaw('lower(naam) like ?', ['%vakantie%'])->firstOrFail();

        // User + token
        $plainPassword = '12345678';
        $employee = User::factory()->create([
            'email'    => 'medewerker@geoprofs.nl',
            'password' => bcrypt($plainPassword),
            'role_id'  => $employeeRole->role_id,
        ]);

        $token = $employee->createToken('test-token')->plainTextToken;

        // Payload
        $startDatum = '2025-12-10';
        $eindDatum  = '2025-12-12';
        $reden      = 'Vakantie met familie naar Spanje';

        // 1) Create (API)
        $createResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/verlof/aanvragen', [
                'verlof_type_id' => $vakantieType->verlof_type_id,
                'start_datum'    => $startDatum,
                'eind_datum'     => $eindDatum,
                'reden'          => $reden,
            ]);

        $createResponse->assertSuccessful();

        // 2) Assert DB: datums worden als datetime opgeslagen (00:00:00)
        $this->assertDatabaseHas('verlofaanvraag', [
            'user_id'        => $employee->user_id,
            'verlof_type_id' => $vakantieType->verlof_type_id,
            'start_datum'    => $startDatum . ' 00:00:00',
            'eind_datum'     => $eindDatum . ' 00:00:00',
            'reden'          => $reden,
        ]);

        // 3) GET mijn aanvragen: check dat reden exact terugkomt
        $listResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/verlof/mijn-aanvragen');

        $listResponse->assertSuccessful();

        // We gokken niet op exacte response-structuur; we eisen wél dat reden exact voorkomt.
        $listResponse->assertJsonFragment([
            'reden' => $reden,
        ]);
    }
}
