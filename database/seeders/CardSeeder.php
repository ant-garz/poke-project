<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardSet;
use App\Models\Pokemon;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $sets = CardSet::all();

        // If no pokemon exist yet, skip safely
        if (Pokemon::count() === 0) {
            $this->command->warn('No Pokemon found. Skipping CardSeeder.');
            return;
        }

        Card::factory()
            ->count(30)
            ->make()
            ->each(function ($card) use ($sets) {

                $card->pokemon_id = Pokemon::inRandomOrder()->first()->id;
                $card->card_set_id = $sets->random()->id;

                $card->save();
            });
    }
}