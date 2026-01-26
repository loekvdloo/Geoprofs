<?php

namespace Tests\Feature\US01001;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TC_US01001_ADM_01_Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_failed_login_attempts_of_existing_user(): void
    {
        $this->seed(RoleSeeder::class);

        $adminRole    = Role::where('role_naam', 'Admin')->firstOrFail();
        $employeeRole = Role::where('role_naam', 'Medewerker')->firstOrFail();

        $employee = User::factory()->create([
            'email' => 'medewerker@geoprofs.nl',
            'password' => bcrypt('12345678'),
            'role_id' => $employeeRole->role_id,
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->role_id,
        ]);

        $this->postJson('/api/login', [
            'email' => $employee->email,
            'password' => 'WRONGPASSWORD'
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $employee->user_id,
            'succes' => false,
            'failure_reason' => 'bad_credentials'
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/login-attempts');

        $response->assertStatus(200);
        $response->assertJsonFragment(['user_email' => $employee->email]);
    }
}
