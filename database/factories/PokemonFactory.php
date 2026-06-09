<?php

namespace Database\Factories;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PokemonFactory extends Factory
{
    protected $model = Pokemon::class;

    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'pokedex_number' => fake()->unique()->numberBetween(1, 9999),

            'name' => $name,
            'slug' => Str::slug($name . '-' . fake()->unique()->numberBetween(1000, 9999)),

            'is_default' => true,
            'base_pokemon_id' => null,

            'hp' => fake()->numberBetween(30, 150),
            'attack' => fake()->numberBetween(30, 150),
            'defense' => fake()->numberBetween(30, 150),
            'special_attack' => fake()->numberBetween(30, 150),
            'special_defense' => fake()->numberBetween(30, 150),
            'speed' => fake()->numberBetween(30, 150),

            'height' => fake()->numberBetween(1, 20),
            'weight' => fake()->numberBetween(1, 500),

            'base_experience' => fake()->numberBetween(50, 300),

            'description' => fake()->sentence(),

            'sprite_url' => null,
            'artwork_url' => null,
            'cry_url' => null,
        ];
    }
}