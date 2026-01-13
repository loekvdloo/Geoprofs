<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AfdelingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('afdeling')->updateOrInsert(
            ['afdeling_naam' => 'Afdeling A'],
            ['afdeling_naam' => 'Afdeling A']
        );

        DB::table('afdeling')->updateOrInsert(
            ['afdeling_naam' => 'Afdeling B'],
            ['afdeling_naam' => 'Afdeling B']
        );

        DB::table('afdeling')->updateOrInsert(
            ['afdeling_naam' => 'Afdeling C'],
            ['afdeling_naam' => 'Afdeling C']
        );
    }
}
