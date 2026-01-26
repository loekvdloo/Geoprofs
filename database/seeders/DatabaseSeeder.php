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

        $this->call([
            VerloftypeSeeder::class,
            RoleSeeder::class,
            AfdelingSeeder::class,
            UsersSeeder::class,
        ]);

    }
}
