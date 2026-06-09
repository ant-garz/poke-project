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
        return [
            'pokemon_id' => Pokemon::factory(), // OK for testing only
            'card_set_id' => CardSet::factory(),

            'external_id' => (string) Str::uuid(),

            'source_tcgdex_id' => fake()->unique()->uuid(),

            'name' => fake()->words(2, true),

            'supertype' => 'Pokémon',
            'subtypes' => [],

            'rarity' => fake()->randomElement([
                'Common',
                'Uncommon',
                'Rare',
                'Rare Holo'
            ]),

            'number' => (string) fake()->numberBetween(1, 300),

            'image_url' => null,

            'hp' => fake()->optional()->numberBetween(30, 300),

            'raw_data' => null,
        ];
    }
}