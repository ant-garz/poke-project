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

                $card->card_set_id = $sets->random()->id;

                $card->source_tcgdex_id = fake()->uuid();

                // Decide what type of card this is
                $supertype = fake()->randomElement([
                    'Pokémon',
                    'Trainer',
                    'Energy',
                ]);

                $card->supertype = $supertype;

                if ($supertype === 'Pokémon') {

                    $pokemon = Pokemon::inRandomOrder()->first();

                    $card->cardable_type = Pokemon::class;
                    $card->cardable_id = $pokemon?->id;

                    // optional: sync name/HP realism
                    $card->name = $pokemon?->name;
                    $card->hp = $pokemon?->hp;

                } else {

                    // Trainer / Energy cards have no Pokémon relation
                    $card->cardable_type = null;
                    $card->cardable_id = null;

                    $card->name = fake()->words(2, true);

                    $card->hp = null;
                }

                $card->save();
            });
    }
}