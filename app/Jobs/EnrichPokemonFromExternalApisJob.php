<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichPokemonFromExternalApisJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        // prevent double runs
        if ($pokemon->source_pokeapi_synced_at !== null && $pokemon->source_tcgdex_synced_at !== null) {
            return;
        }

        /**
         * ONLY DISPATCH — NO API CALLS HERE
         */
        if($pokemon->source_pokeapi_synced_at === null){
            FetchPokeApiDataJob::dispatch($pokemon->id)->delay(now()->addSeconds(1));
        }

        if($pokemon->source_tcgdex_synced_at === null){
            FetchTcgdexDataJob::dispatch($pokemon->id)->delay(now()->addSeconds(2));
        }
    }
}