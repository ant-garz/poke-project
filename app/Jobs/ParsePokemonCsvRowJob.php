<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Models\Type;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ParsePokemonCsvRowJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $row) {}

    public function handle(): void
    {
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
         * 2. Resolve types (CSV → DB)
         * - ensure Type records exist
         */
        $typeIds = collect([$type1, $type2])
            ->filter() // removes null/empty
            ->map(fn ($typeName) => Type::firstOrCreate([
                'name' => ucfirst(trim($typeName)),
                'slug' => Str::slug($typeName),
            ]))
            ->pluck('id')
            ->values()
            ->all();

        /**
         * 3. Enforce business rule: max 2 types
         */
        if (count($typeIds) > 2) {
            Log::warning("Pokemon {$pokemon->id} has more than 2 types in CSV. Trimming.");
            $typeIds = array_slice($typeIds, 0, 2);
        }

        /**
         * 4. Sync pivot table (authoritative CSV source)
         */
        $pokemon->types()->sync($typeIds);

        /**
         * 5. Kick off enrichment pipeline
         */
        EnrichPokemonFromExternalApisJob::dispatch($pokemon->id);
    }
}