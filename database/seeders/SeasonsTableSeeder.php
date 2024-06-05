<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('seasons')->insert([
            'name' => 'Seizoen 2024-2025',
            'start_date' => '2024-09-06', // Pas deze datums aan op basis van jouw behoeften
            'end_date' => '2025-05-02',
        ]);
    }
}

