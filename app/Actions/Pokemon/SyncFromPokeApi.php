<?php

namespace App\Actions\Pokemon;

use App\Models\Pokemon;
use Illuminate\Support\Facades\Http;

class SyncFromPokeApi
{
    public function execute(int $pokemonId): Pokemon
    {
        $pokemon = Pokemon::findOrFail($pokemonId);

        $url = "https://pokeapi.co/api/v2/pokemon/{$pokemon->pokedex_number}";

        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            throw new \Exception("PokeAPI sync failed for Pokémon {$pokemon->id}");
        }

        $data = $response->json();

        $sprites = $data['sprites'] ?? [];
        $other = $sprites['other'] ?? [];

        $pokemon->update([
            // Core stats
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'base_experience' => $data['base_experience'] ?? null,

            // Missing but important
            'cry_url' => $data['cries']['latest']
                ?? $data['cries']['legacy']
                ?? null,

            // visuals
            'sprite_url' => $sprites['front_default'] ?? null,

            'artwork_url' =>
                $other['official-artwork']['front_default']
                ?? $other['home']['front_default']
                ?? null,

            // raw storage
            'raw_pokeapi' => $data,
            'source_pokeapi_synced_at' => now(),
        ]);

        return $pokemon->fresh();
    }
}