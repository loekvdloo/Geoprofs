<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'voornaam' => 'Test',
            'achternaam' => 'User',
            'email' => 'kameel@kameel.com',
            'password' => Hash::make('12345678'),
            'telefoonnummer' => '0612345678',
            'account_status' => true,
        ]);

        $this->call([
            VerloftypeSeeder::class,
            RoleSeeder::class,
            AfdelingSeeder::class,
            UsersSeeder::class,
        ]);

    }
}
