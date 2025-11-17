<?php

namespace Tests\Feature\Ahmad;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginLoggingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_successful_login_is_logged(): void
    {
        $userId = DB::table('users')->insertGetId([
            'voornaam'       => 'Medewerker',
            'achternaam'     => 'Test',
            'email'          => 'medewerker@geoprofs.nl',
            'password'       => bcrypt('12345678'),
            'account_status' => true,
            'verlofsaldo'    => 25,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $res = $this->postJson('/api/login', [
            'email'    => 'medewerker@geoprofs.nl',
            'password' => '12345678',
        ]);

        $res->assertOk();

        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $userId,
            'succes'  => true,
        ]);
    }
}
