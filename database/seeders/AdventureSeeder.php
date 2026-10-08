<?php

namespace Database\Seeders;

use App\Models\Adventure;
use Illuminate\Database\Seeder;

class AdventureSeeder extends Seeder
{
    public function run(): void
    {
        Adventure::factory()
            ->count(3)
            ->create();
    }
}
