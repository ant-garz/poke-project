<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

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

        $pokemon = Pokemon::updateOrCreate(
            ['pokedex_number' => $number],
            [
                'name' => $name,
                'slug' => Str::slug($name),

                'hp' => $hp,
                'attack' => $attack,
                'defense' => $defense,
                'special_attack' => $spAttack,
                'special_defense' => $spDefense,
                'speed' => $speed,
            ]
        );

        EnrichPokemonFromExternalApisJob::dispatch($pokemon->id);
    }
}
