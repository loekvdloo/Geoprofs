<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Rollen
        Role::insert([
            ['role_id' => 1, 'role_naam' => 'Admin'],
            ['role_id' => 2, 'role_naam' => 'Manager'],
        ]);

        // Afdelingen
        Afdeling::insert([
            ['afdeling_id' => 1, 'afdeling_naam' => 'IT'],
            ['afdeling_id' => 2, 'afdeling_naam' => 'HR'],
        ]);

        // Admin gebruiker
        $this->admin = User::create([
            'voornaam' => 'Admin',
            'achternaam' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'afdeling_id' => 1,
        ]);

        // Normale gebruiker
        $this->user = User::create([
            'voornaam' => 'John',
            'achternaam' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => 2,
            'afdeling_id' => 1,
        ]);
    }

    public function test_admin_can_get_all_users()
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'admin@example.com'])
            ->assertJsonFragment(['email' => 'john@example.com']);
    }

    public function test_admin_can_update_user_role_and_afdeling()
    {
        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/users/{$this->user->user_id}/role-afdeling", [
                'role_id' => 1,
                'afdeling_id' => 2,
            ])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Rol en afdeling bijgewerkt',
            ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $this->user->user_id,
            'role_id' => 1,
            'afdeling_id' => 2,
        ]);
    }

    public function test_non_admin_cannot_access_endpoints()
    {
        $nonAdmin = User::create([
            'voornaam' => 'Piet',
            'achternaam' => 'Tester',
            'email' => 'piet@example.com',
            'password' => bcrypt('password'),
            'role_id' => 2,
            'afdeling_id' => 1,
        ]);

        $this->actingAs($nonAdmin, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        $this->actingAs($nonAdmin, 'sanctum')
            ->putJson("/api/admin/users/{$this->user->user_id}/role-afdeling", [
                'role_id' => 1,
                'afdeling_id' => 1,
            ])
            ->assertStatus(403);
    }
}
