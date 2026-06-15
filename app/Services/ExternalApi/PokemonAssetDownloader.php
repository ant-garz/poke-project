<?php

namespace App\Services\ExternalApi;

use App\Models\Pokemon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PokemonAssetDownloader
{
    public function downloadPokemonAssets(
        Pokemon $pokemon,
        array $pokeApiData
    ): array {
        $stored = [];

        /*
        |--------------------------------------------------------------------------
        | Official Artwork
        |--------------------------------------------------------------------------
        */

        $artworkUrl = data_get(
            $pokeApiData,
            'sprites.other.official-artwork.front_default'
        );

        if ($artworkUrl) {
            $stored['artwork'] = $this->downloadFile(
                $artworkUrl,
                "pokemon/{$pokemon->pokedex_number}/artwork.png"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sprite
        |--------------------------------------------------------------------------
        */

        $spriteUrl = data_get(
            $pokeApiData,
            'sprites.front_default'
        );

        if ($spriteUrl) {
            $stored['sprite'] = $this->downloadFile(
                $spriteUrl,
                "pokemon/{$pokemon->pokedex_number}/sprite.png"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cries
        |--------------------------------------------------------------------------
        */

        $latestCry = data_get(
            $pokeApiData,
            'cries.latest'
        );

        if ($latestCry) {
            $stored['cry_latest'] = $this->downloadFile(
                $latestCry,
                "pokemon/{$pokemon->pokedex_number}/cries/latest.ogg"
            );
        }

        $legacyCry = data_get(
            $pokeApiData,
            'cries.legacy'
        );

        if ($legacyCry) {
            $stored['cry_legacy'] = $this->downloadFile(
                $legacyCry,
                "pokemon/{$pokemon->pokedex_number}/cries/legacy.ogg"
            );
        }

        return $stored;
    }

    public function downloadTcgCardAssets(
        Pokemon $pokemon,
        string $baseAssetUrl
    ): array {
        $stored = [];

        $qualities = ['high', 'low'];
        $extensions = ['webp', 'png', 'jpg'];

        foreach ($qualities as $quality) {
            foreach ($extensions as $extension) {

                $url = "{$baseAssetUrl}/{$quality}.{$extension}";

                $path = "pokemon/{$pokemon->pokedex_number}/cards/{$quality}.{$extension}";

                $key = "{$quality}_{$extension}";

                $stored[$key] = $this->downloadFile($url, $path);
            }
        }

        return $stored;
    }

    protected function downloadFile(
        string $url,
        string $path
    ): ?string {
        $response = Http::timeout(60)->get($url);

        if ($response->failed()) {
            return null;
        }

        Storage::disk('public')->put(
            $path,
            $response->body()
        );

        return $path;
    }
}