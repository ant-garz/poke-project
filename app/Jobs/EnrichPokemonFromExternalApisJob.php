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
        $pokemon = Pokemon::with('types')->findOrFail($this->pokemonId);

        /**
         * 1. Prevent duplicate enrichment runs
         */
        if ($pokemon->is_enriched) {
            return;
        }

        /**
         * 2. Mark as processing (optional but recommended)
         */
        $pokemon->update([
            'is_enriched' => false, // still processing
        ]);

        /**
         * 3. Dispatch external API fetchers
         * (each job handles its own rate limits + retries)
         */
        FetchPokeApiDataJob::dispatch($pokemon->id);

        FetchTcgdexDataJob::dispatch($pokemon->id);
    }
}