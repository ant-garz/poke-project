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
            'raw_pokeapi' =>  $this->normalize_to_array($data),
            'source_pokeapi_synced_at' => now(),
        ]);

        // DownloadPokemonAssetsJob::dispatch($pokemon->id);
    }

    public function normalize_to_array(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof \JsonSerializable) {
            return (array) $value->jsonSerialize();
        }

        if (is_object($value)) {
            return json_decode(
                json_encode($value),
                true
            ) ?? [];
        }

        return [];
    }
}