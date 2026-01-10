<?php

namespace Tests\Feature\US01001;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnknownEmailLoginAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_unknown_email_login_attempt(): void
    {
        // 1. Seed roles (Admin, Medewerker, etc.)
        $this->seed(RoleSeeder::class);

        // 2. Haal Admin role record op
        $adminRole = Role::where('role_naam', 'Admin')->firstOrFail();

        // 3. Maak een admin user aan
        $admin = User::factory()->create([
            'email'    => 'admin@geoprofs.nl',
            'password' => bcrypt('admin123'),
            'role_id'  => $adminRole->role_id,
        ]);

        // 4. Doe login met een email die NIET bestaat in DB
        $this->postJson('/api/login', [
            'email'    => 'nonexisting@geoprofs.nl',
            'password' => 'whatever',
        ]);

        // 5. Check database: login attempt voor unknown email
        $this->assertDatabaseHas('login_attempts', [
            'user_id'        => null,
            'succes'         => false,
            'failure_reason' => 'unknown_email',
        ]);

        // 6. Admin logt in en haalt overzicht van attempts op
        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/login-attempts');

        $response->assertStatus(200);

        // 7. Admin moet de 'unknown_email' attempt zien
        $response->assertJsonFragment([
            'failure_reason' => 'unknown_email',
        ]);
    }
}
