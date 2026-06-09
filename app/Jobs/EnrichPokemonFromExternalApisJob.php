<?php

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
        if ($pokemon->is_enriched) {
            return;
        }

        // mark as queued (not finished)
        $pokemon->update([
            'is_enriched' => false,
        ]);

        /**
         * ONLY DISPATCH — NO API CALLS HERE
         */
        FetchPokeApiDataJob::dispatch($pokemon->id)->delay(now()->addSeconds(1));

        FetchTcgdexDataJob::dispatch($pokemon->id)->delay(now()->addSeconds(2));
    }
}