<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchTcgCardDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        /**
         * 1. Fetch card data
         */
        $response = Http::get(
            "https://api.tcgdex.net/v2/en/cards/" . $pokemon->pokedex_number
        );

        if ($response->failed()) {
            return;
        }

        $card = $response->json();

        /**
         * 2. Extract fields safely (NO assumptions)
         */
        $description = $card['description'] ?? null;

        $artworkUrl = $card['image'] ?? null;

        /**
         * 3. Update Pokémon (TCG-owned fields only)
         */
        $pokemon->update([
            'description' => $description,
            'artwork_url' => $artworkUrl,
            'raw_tcgdex' => $card,
            'source_tcgdex_synced_at' => now(),
        ]);
    }
}