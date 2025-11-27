<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Zorg dat roles bestaan
        $this->call(RoleSeeder::class);

        // Haal role_id's op
        $adminRoleId = DB::table('roles')->where('role_naam', 'Admin')->value('role_id');
        $medRoleId   = DB::table('roles')->where('role_naam', 'Medewerker')->value('role_id');

        if (!$adminRoleId || !$medRoleId) {
            throw new \RuntimeException('Roles ontbreken (Admin/Medewerker).');
        }

        $now = now();

        // Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@geoprofs.nl'],
            [
                'afdeling_id'    => null,
                'role_id'        => $adminRoleId,
                'voornaam'       => 'Admin',
                'achternaam'     => 'Geo',
                'telefoonnummer' => '0612345678',
                'password'       => Hash::make('12345678'),
                'account_status' => true,
                'verlofsaldo'    => 25,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]
        );

        // Medewerker
        DB::table('users')->updateOrInsert(
            ['email' => 'medewerker@geoprofs.nl'],
            [
                'afdeling_id'    => null,
                'role_id'        => $medRoleId,
                'voornaam'       => 'Medewerker',
                'achternaam'     => 'Test',
                'telefoonnummer' => '0698765432',
                'password'       => Hash::make('12345678'),
                'account_status' => true,
                'verlofsaldo'    => 25,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]
        );
    }
}
