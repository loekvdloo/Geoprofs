<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Rollen ophalen op naam (geen harde IDs)
        $adminRoleId   = DB::table('roles')->where('role_naam', 'Admin')->value('role_id');
        $managerRoleId = DB::table('roles')->where('role_naam', 'Manager')->value('role_id');
        $employeeRoleId = DB::table('roles')->where('role_naam', 'Medewerker')->value('role_id');

        // Afdelingen ophalen uit jouw AfdelingSeeder output
        // Resultaat: ['ICT' => 1, 'HR' => 2, ...]
        $afdelingen = DB::table('afdeling')->pluck('afdeling_id', 'afdeling_naam');

        // Fail-fast als afdelingen ontbreken (anders seed je lege troep)
        if ($afdelingen->count() === 0) {
            throw new \RuntimeException("Geen afdelingen gevonden. Run eerst AfdelingSeeder.");
        }

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@geoprofs.nl'],
            [
                'voornaam' => 'Admin',
                'achternaam' => 'GeoProfs',
                'email' => 'admin@geoprofs.nl',
                'password' => Hash::make('12345678'),
                'role_id' => $adminRoleId,
                'afdeling_id' => null,
                'verlofsaldo' => 25,
                'account_status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Per afdeling: 1 manager + 3 medewerkers
        |--------------------------------------------------------------------------
        */
        foreach ($afdelingen as $afdelingNaam => $afdelingId) {
            $slug = strtolower($afdelingNaam); // ict, hr, finance, planning

            // Manager
            DB::table('users')->updateOrInsert(
                ['email' => "manager.$slug@geoprofs.nl"],
                [
                    'voornaam' => 'Manager',
                    'achternaam' => $afdelingNaam,
                    'email' => "manager.$slug@geoprofs.nl",
                    'password' => Hash::make('12345678'),
                    'role_id' => $managerRoleId,
                    'afdeling_id' => $afdelingId,
                    'verlofsaldo' => 25,
                    'account_status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // 3 medewerkers
            for ($i = 1; $i <= 3; $i++) {
                DB::table('users')->updateOrInsert(
                    ['email' => "medewerker{$i}.$slug@geoprofs.nl"],
                    [
                        'voornaam' => 'Medewerker',
                        'achternaam' => "{$i} $afdelingNaam",
                        'email' => "medewerker{$i}.$slug@geoprofs.nl",
                        'password' => Hash::make('12345678'),
                        'role_id' => $employeeRoleId,
                        'afdeling_id' => $afdelingId,
                        'verlofsaldo' => 18 + $i, // beetje variatie (19,20,21)
                        'account_status' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Test gebruiker voor mail / notificatie demo
        |--------------------------------------------------------------------------
        | Zet hem in ICT (bestaat altijd in jouw AfdelingSeeder)
        */
        $ictId = $afdelingen->get('ICT'); // null als iemand ICT hernoemt, maar bij jou bestaat het

        DB::table('users')->updateOrInsert(
            ['email' => '1206993@student.roc-nijmegen.nl'],
            [
                'voornaam' => 'Test',
                'achternaam' => 'Student',
                'email' => '1206993@student.roc-nijmegen.nl',
                'password' => Hash::make('12345678'),
                'role_id' => $employeeRoleId,
                'afdeling_id' => $ictId,
                'verlofsaldo' => 20,
                'account_status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
