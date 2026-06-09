<?php
namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchPokeApiDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $response = Http::get("https://pokeapi.co/api/v2/pokemon/" . strtolower($pokemon->name));

        if ($response->failed()) {
            return;
        }

        $data = $response->json();

        $pokemon->update([
            'height' => $data['height'] ?? null,
            'base_experience' => $data['base_experience'] ?? null,
            'raw_pokeapi' => $data, // optional JSON column recommended
        ]);
    }
}
