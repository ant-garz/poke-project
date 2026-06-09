<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CardAttack;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardAttackFactory extends Factory
{
    protected $model = CardAttack::class;

    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),

            'name' => fake()->words(2, true),

            'damage' => fake()->optional()->numberBetween(10, 200),

            'effect' => fake()->optional()->sentence(),

            'cost' => [
                fake()->randomElement(['Fire', 'Water', 'Grass', 'Colorless'])
            ],
        ];
    }
}