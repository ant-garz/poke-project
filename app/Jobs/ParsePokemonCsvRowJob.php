<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Models\Type;
use App\Models\PokemonImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ParsePokemonCsvRowJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $batchId,
        public array $row
    ) {}

    public function handle(): void
    {
        $batch = PokemonImportBatch::find($this->batchId);

        try {
            [
                $number,
                $name,
                $type1,
                $type2,
                $hp,
                $attack,
                $defense,
                $spAttack,
                $spDefense,
                $speed
            ] = $this->row;

            /**
             * 1. Create or update Pokémon core data
             */
            $pokemon = Pokemon::updateOrCreate(
                ['pokedex_number' => (int) $number],
                [
                    'name' => $name,
                    'slug' => Str::slug($name . '-' . $number),

                    'hp' => (int) $hp,
                    'attack' => (int) $attack,
                    'defense' => (int) $defense,
                    'special_attack' => (int) $spAttack,
                    'special_defense' => (int) $spDefense,
                    'speed' => (int) $speed,
                ]
            );

            /**
             * 2. Resolve types
             */
            $typeIds = collect([$type1, $type2])
                ->filter()
                ->map(fn ($typeName) => Type::firstOrCreate([
                    'name' => ucfirst(trim($typeName)),
                    'slug' => Str::slug($typeName),
                ]))
                ->pluck('id')
                ->values()
                ->all();

            /**
             * 3. Enforce max 2 types
             */
            if (count($typeIds) > 2) {
                Log::warning("Pokemon {$pokemon->id} has more than 2 types. Trimming.");
                $typeIds = array_slice($typeIds, 0, 2);
            }

            /**
             * 4. Sync pivot
             */
            $pokemon->types()->sync($typeIds);

            /**
             * 5. Kick off enrichment
             */
            EnrichPokemonFromExternalApisJob::dispatch($pokemon->id);

            /**
             * 6. Mark success
             */
            if ($batch) {
                $batch->increment('processed_rows');
            }

        } catch (\Throwable $e) {

            Log::error('Failed parsing Pokémon CSV row', [
                'row' => $this->row,
                'error' => $e->getMessage(),
            ]);

            /**
             * Mark failure
             */
            if ($batch) {
                $batch->increment('failed_rows');
            }

            // optionally rethrow if you want queue retry behavior
            // throw $e;
        }
    }
}