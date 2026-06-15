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
            $number = $this->row['Number'] ?? null;
            $name = $this->row['Name'] ?? null;

            if (!$number || !$name) {
                throw new \Exception("Missing required CSV fields");
            }

            $type1 = $this->row['Type 1'] ?? null;
            $type2 = $this->row['Type 2'] ?? null;

            $hp = $this->row['HP'] ?? 0;
            $attack = $this->row['Attack'] ?? 0;
            $defense = $this->row['Defense'] ?? 0;
            $spAttack = $this->row['Sp.Attack'] ?? 0;
            $spDefense = $this->row['Sp.Defense'] ?? 0;
            $speed = $this->row['Speed'] ?? 0;

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

            $primaryType = null;
            $secondaryType = null;

            if ($type1) {
                $primaryType = Type::firstOrCreate(
                    ['slug' => Str::slug(trim($type1))],
                    ['name' => ucfirst(strtolower(trim($type1)))]
                );
            }

            if ($type2) {
                $secondaryType = Type::firstOrCreate(
                    ['slug' => Str::slug(trim($type2))],
                    ['name' => ucfirst(strtolower(trim($type2)))]
                );
            }

            $pokemon->update([
                'primary_type_id' => $primaryType?->id,
                'secondary_type_id' => $secondaryType?->id,
            ]);

            EnrichPokemonFromExternalApisJob::dispatch($pokemon->id);

            $batch?->increment('processed_rows');

        } catch (\Throwable $e) {

            Log::error('Failed parsing Pokémon CSV row', [
                'row' => $this->row,
                'error' => $e->getMessage(),
            ]);

            $batch?->increment('failed_rows');
        }
    }
}