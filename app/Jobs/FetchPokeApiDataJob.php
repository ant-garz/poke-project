<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchPokeApiDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail(
            $this->pokemonId
        );

        $response = Http::timeout(30)
            ->get(
                "https://pokeapi.co/api/v2/pokemon/{$pokemon->pokedex_number}"
            );

        if ($response->failed()) {
            return;
        }

        $data = $response->json();

        $pokemon->update([
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'base_experience' => $data['base_experience'] ?? null,

            'raw_pokeapi' => $data,

            'source_pokeapi_synced_at' => now(),
        ]);

        DownloadPokemonAssetsJob::dispatch(
            $pokemon->id
        )->onQueue('assets');
    }
}