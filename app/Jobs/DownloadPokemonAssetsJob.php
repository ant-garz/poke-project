<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Services\ExternalApi\PokemonAssetDownloader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DownloadPokemonAssetsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(PokemonAssetDownloader $downloader): void
    {
        $pokemon = Pokemon::find($this->pokemonId);

        if (! $pokemon) {
            return;
        }

        $this->downloadPokeSprites($pokemon);
        $this->downloadCry($pokemon);
        $this->downloadTcgImage($pokemon);

        $pokemon->update([
            'sprite_url' => $assets['sprite'] ?? null,
            'artwork_url' => $assets['artwork'] ?? null,
            'cry_url' => $assets['cry_latest'] ?? null,
        ]);
    }

    private function downloadToStorage(string $url, string $path): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                logger()->warning('Asset download failed', [
                    'url' => $url,
                    'path' => $path,
                    'status' => $response->status(),
                ]);
                return null;
            }

            Storage::disk('public')->put($path, $response->body());

            return Storage::url($path);

        } catch (\Throwable $e) {
            logger()->error('Asset download exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function downloadPokeSprites(Pokemon $pokemon): void
    {
        $data = $pokemon->raw_pokeapi;

        if (!$data) {
            logger()->warning('No PokeAPI data for sprite download', [
                'pokemon_id' => $pokemon->id,
            ]);
            return;
        }

        $basePath = "pokemon/{$pokemon->id}/pokeapi";

        $spriteUrl = $data['sprites']['front_default'] ?? null;
        $artworkUrl = $data['sprites']['other']['official-artwork']['front_default'] ?? null;

        if ($spriteUrl) {
            $path = "{$basePath}/sprite.png";

            $stored = $this->downloadToStorage($spriteUrl, $path);

            if ($stored) {
                $pokemon->update(['sprite_url' => $stored]);
            }
        }

        if ($artworkUrl) {
            $path = "{$basePath}/artwork.png";

            $stored = $this->downloadToStorage($artworkUrl, $path);

            if ($stored) {
                $pokemon->update(['artwork_url' => $stored]);
            }
        }
    }

    private function downloadCry(Pokemon $pokemon): void
    {
        $data = $pokemon->raw_pokeapi;

        if (!$data) {
            return;
        }

        $cryUrl = $data['cries']['latest']
            ?? $data['cries']['legacy']
            ?? null;

        if (!$cryUrl) {
            logger()->info('No cry available', [
                'pokemon_id' => $pokemon->id,
            ]);
            return;
        }

        $path = "pokemon/{$pokemon->id}/pokeapi/cry.ogg";

        $stored = $this->downloadToStorage($cryUrl, $path);

        if ($stored) {
            $pokemon->update([
                'cry_url' => $stored,
            ]);
        }
    }

    private function downloadTcgImage(Pokemon $pokemon): void
    {
        $card = $pokemon->raw_tcgdex;

        if (!$card) {
            logger()->warning('Missing TCG card data', [
                'pokemon_id' => $pokemon->id,
            ]);
            return;
        }

        $setId = $card['set']['id'] ?? null;
        $localId = $card['localId'] ?? null;

        if (!$setId || !$localId) {
            logger()->warning('Invalid TCG card structure', [
                'pokemon_id' => $pokemon->id,
                'set' => $setId,
                'localId' => $localId,
            ]);
            return;
        }

        // build asset URL (recommended format)
        $imageUrl = "https://assets.tcgdex.net/en/{$setId}/{$localId}/high.webp";

        $path = "pokemon/{$pokemon->id}/tcg/card.webp";

        $stored = $this->downloadToStorage($imageUrl, $path);
    }
}