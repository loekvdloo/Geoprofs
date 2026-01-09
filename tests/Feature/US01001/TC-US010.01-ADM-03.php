<?php

namespace Tests\Feature\US01001;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NonAdminCannotAccessLoginAttemptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_login_attempts(): void
    {
        // 1. Seed roles
        $this->seed(RoleSeeder::class);

        // 2. Haal de medewerker / niet-admin rol op
        $employeeRole = Role::where('role_naam', 'Medewerker')->firstOrFail();

        // 3. Maak een medewerker user aan
        $employee = User::factory()->create([
            'email'    => 'employee@geoprofs.nl',
            'password' => bcrypt('12345678'),
            'role_id'  => $employeeRole->role_id,
        ]);

        // 4. Medewerker probeert toegang te krijgen tot admin endpoint
        $response = $this
            ->actingAs($employee, 'sanctum')
            ->getJson('/api/admin/login-attempts');

        // 5. Verwacht resultaat: 403 Forbidden
        $response->assertStatus(403);

        // Extra check: correcte melding
        $response->assertJsonFragment([
            'message' => 'Forbidden',
        ]);
    }
}
