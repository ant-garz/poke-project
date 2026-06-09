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

        // prevent re-processing later
        if ($pokemon->is_enriched ?? false) {
            return;
        }

        FetchPokeApiDataJob::dispatch($pokemon->id);
        FetchTcgdexDataJob::dispatch($pokemon->id);
    }
}