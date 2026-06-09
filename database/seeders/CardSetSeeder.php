<?php

namespace Database\Seeders;

use App\Models\CardSet;
use Illuminate\Database\Seeder;

class CardSetSeeder extends Seeder
{
    public function run(): void
    {
        CardSet::factory()
            ->count(5)
            ->create([
                'raw_data' => null,
            ]);
    }
}