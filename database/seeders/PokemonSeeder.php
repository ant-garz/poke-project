<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;

class PokemonSeeder extends Seeder
{
    public function run(): void
    {
        Pokemon::factory()->count(10)->create();
    }
}