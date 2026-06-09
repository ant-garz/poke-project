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
        $name = fake()->unique()->firstName();

        return [
            'pokedex_number' => fake()->unique()->numberBetween(1, 9999),

            'name' => ucfirst($name),
            'slug' => Str::slug($name),

            // Option B hybrid defaults
            'is_default' => true,
            'base_pokemon_id' => null,

            // Stats
            'hp' => fake()->numberBetween(30, 150),
            'attack' => fake()->numberBetween(30, 150),
            'defense' => fake()->numberBetween(30, 150),
            'special_attack' => fake()->numberBetween(30, 150),
            'special_defense' => fake()->numberBetween(30, 150),
            'speed' => fake()->numberBetween(30, 150),

            // Optional enrichment fields (kept simple for factory)
            'height' => fake()->optional()->numberBetween(1, 20),
            'weight' => fake()->optional()->numberBetween(1, 500),

            'base_experience' => fake()->optional()->numberBetween(50, 300),

            'description' => fake()->optional()->sentence(),

            'sprite_url' => null,
            'artwork_url' => null,
            'cry_url' => null,
        ];
    }
}