<?php

namespace Database\Factories;

use App\Models\CardSet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CardSetFactory extends Factory
{
    protected $model = CardSet::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'external_id' => Str::slug($name) . '-' . fake()->numberBetween(1, 9999),

            'name' => ucwords($name),
            'series' => fake()->randomElement([
                'Sword & Shield',
                'Sun & Moon',
                'Scarlet & Violet'
            ]),

            'logo_url' => null,
            'symbol_url' => null,

            'release_date' => fake()->optional()->date(),

            'card_count_official' => fake()->numberBetween(50, 250),
            'card_count_total' => fake()->numberBetween(50, 300),

            'raw_data' => null,
        ];
    }
}