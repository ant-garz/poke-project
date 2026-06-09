<?php

namespace Database\Factories;

use App\Models\PokemonImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class PokemonImportBatchFactory extends Factory
{
    protected $model = PokemonImportBatch::class;

    public function definition(): array
    {
        return [
            'original_filename' => 'pokemon_sample.csv',
            'file_path' => 'pokemon-imports/sample.csv',

            'status' => $this->faker->randomElement([
                'uploaded',
                'processing',
                'completed',
            ]),

            'total_rows' => 1000,
            'processed_rows' => $this->faker->numberBetween(0, 1000),
            'failed_rows' => $this->faker->numberBetween(0, 20),

            'meta' => null,
        ];
    }
}