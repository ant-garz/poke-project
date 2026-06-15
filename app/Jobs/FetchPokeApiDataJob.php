<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use App\Services\ExternalApi\ExternalApiLogger;

class FetchPokeApiDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $url = "https://pokeapi.co/api/v2/pokemon/{$pokemon->pokedex_number}";

        $response = Http::timeout(30)->get($url);

        ExternalApiLogger::log(
            'POKEAPI',
            $url,
            $response,
            ['pokemon_id' => $pokemon->id]
        );

        if ($response->failed()) {
            ExternalApiLogger::error('POKEAPI_FAILED', [
                'pokemon_id' => $pokemon->id,
                'status' => $response->status(),
            ]);

            return;
        }

        $data = $response->json();

        $pokemon->update([
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'base_experience' => $data['base_experience'] ?? null,
            'raw_pokeapi' => json_decode(json_encode($data), true),
            'source_pokeapi_synced_at' => now(),
        ]);

        DownloadPokemonAssetsJob::dispatch($pokemon->id)->onQueue('assets');
    }
}