<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $response = Http::get("https://api.tcgdex.net/v2/en/cards?name=" . urlencode($pokemon->name));

        if ($response->failed()) {
            return;
        }

        $cards = $response->json();

        $pokemon->update([
            'raw_tcgdex' => $cards, // optional JSON column
        ]);

        // later: create Card models here
    }
}