<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Verloftype;


class VerloftypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Verloftype::insert([
            ['naam' => 'Vakantie', 'betaald' => true],
            ['naam' => 'Ziekteverlof', 'betaald' => true],
            ['naam' => 'Onbetaald verlof', 'betaald' => false]
        ]);
    }
}
