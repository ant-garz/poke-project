<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardSet;
use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        $supertype = fake()->randomElement([
            'Pokémon',
            'Trainer',
            'Energy',
        ]);

        $isPokemon = $supertype === 'Pokémon';

        return [
            // Polymorphic relationship (replaces pokemon_id)
            'cardable_type' => $isPokemon ? Pokemon::class : null,
            'cardable_id' => $isPokemon ? Pokemon::factory() : null,

            'card_set_id' => CardSet::factory(),

            'external_id' => (string) Str::uuid(),

            'source_tcgdex_id' => fake()->unique()->uuid(),

            'name' => $isPokemon
                ? fake()->firstName()
                : fake()->words(2, true),

            'supertype' => $supertype,

            'subtypes' => $isPokemon
                ? fake()->randomElements([
                    'Basic',
                    'Stage 1',
                    'Stage 2',
                    'EX',
                    'GX',
                    'V',
                ], 1)
                : [],

            'rarity' => fake()->randomElement([
                'Common',
                'Uncommon',
                'Rare',
                'Rare Holo'
            ]),

            'number' => (string) fake()->numberBetween(1, 300),

            'image_url' => null,

            'hp' => $isPokemon
                ? fake()->numberBetween(30, 300)
                : null,

            'raw_data' => null,
        ];
    }
}