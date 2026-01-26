<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AfdelingSeeder extends Seeder
{
    public function run(): void
    {
        $afdelingen = [
            'ICT',
            'HR',
            'Finance',
            'Planning',
        ];

        foreach ($afdelingen as $naam) {
            DB::table('afdeling')->updateOrInsert(
                ['afdeling_naam' => $naam],
                ['afdeling_naam' => $naam]
            );
        }
    }
}
