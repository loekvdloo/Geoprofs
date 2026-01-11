<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AfdelingSeeder::class,
        ]);

        $adminRoleId    = DB::table('roles')->where('role_naam', 'Admin')->value('role_id');
        $medRoleId      = DB::table('roles')->where('role_naam', 'Medewerker')->value('role_id');
        $managerRoleId  = DB::table('roles')->where('role_naam', 'Manager')->value('role_id');

        $afdelingA = DB::table('afdeling')->where('afdeling_naam', 'Afdeling A')->value('afdeling_id');
        $afdelingB = DB::table('afdeling')->where('afdeling_naam', 'Afdeling B')->value('afdeling_id');
        $afdelingC = DB::table('afdeling')->where('afdeling_naam', 'Afdeling C')->value('afdeling_id');

        $now = now();

        // Admin (mag zonder afdeling)
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@geoprofs.nl'],
            [
                'afdeling_id'    => null,
                'role_id'        => $adminRoleId,
                'voornaam'       => 'Admin',
                'achternaam'     => 'GeoProfs',
                'telefoonnummer' => '0612345678',
                'password'       => Hash::make('12345678'),
                'account_status' => true,
                'verlofsaldo'    => 25,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]
        );

        // 3 managers (elk eigen afdeling)
        $managers = [
            ['manager1@geoprofs.nl', 'Manager', 'One', $afdelingA],
            ['manager2@geoprofs.nl', 'Manager', 'Two', $afdelingB],
            ['manager3@geoprofs.nl', 'Manager', 'Three', $afdelingC],
        ];

        foreach ($managers as [$email, $voornaam, $achternaam, $afdelingId]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'afdeling_id'    => $afdelingId,
                    'role_id'        => $managerRoleId,
                    'voornaam'       => $voornaam,
                    'achternaam'     => $achternaam,
                    'telefoonnummer' => null,
                    'password'       => Hash::make('12345678'),
                    'account_status' => true,
                    'verlofsaldo'    => 25,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );
        }

        // 6 medewerkers (2 per afdeling)
        $medewerkers = [
            ['medewerker1@geoprofs.nl', 'Medewerker', 'One', $afdelingA],
            ['medewerker2@geoprofs.nl', 'Medewerker', 'Two', $afdelingA],

            ['medewerker3@geoprofs.nl', 'Medewerker', 'Three', $afdelingB],
            ['medewerker4@geoprofs.nl', 'Medewerker', 'Four', $afdelingB],

            ['medewerker5@geoprofs.nl', 'Medewerker', 'Five', $afdelingC],
            ['medewerker6@geoprofs.nl', 'Medewerker', 'Six', $afdelingC],
        ];

        foreach ($medewerkers as [$email, $voornaam, $achternaam, $afdelingId]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'afdeling_id'    => $afdelingId,
                    'role_id'        => $medRoleId,
                    'voornaam'       => $voornaam,
                    'achternaam'     => $achternaam,
                    'telefoonnummer' => null,
                    'password'       => Hash::make('12345678'),
                    'account_status' => true,
                    'verlofsaldo'    => 25,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );
        }
    }
}
