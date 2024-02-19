<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Team;

class DivisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $teams = [
            ['name' => 'De jolige stuiters', 'division_id' => 1],
            ['name' => 'De ronde jongens', 'division_id' => 1],
            ['name' => 'De loze ballen', 'division_id' => 1],
            ['name' => 'Square boys', 'division_id' => 2],
            ['name' => 'groene ballen', 'division_id' => 2],
            ['name' => 'De kakkers', 'division_id' => 3],
            ['name' => 'De vogelaars', 'division_id' => 3],
            ['name' => 'the rapi sts', 'division_id' => 3],
            // Add more teams as per your requirement
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }

}
