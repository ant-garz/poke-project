<?php

namespace Database\Factories;

use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),

            'color' => fake()->hexColor(),
            'text_color' => '#ffffff',
            'icon' => null,
        ];
    }
}