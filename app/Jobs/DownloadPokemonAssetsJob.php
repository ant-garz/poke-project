<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Services\PokemonAssetDownloader;
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

        $assets = $downloader->downloadPokemonAssets(
            $pokemon,
            $pokemon->raw_pokeapi ?? []
        );

        $pokemon->update([
            'sprite_url' => $assets['sprite'] ?? null,
            'artwork_url' => $assets['artwork'] ?? null,
            'cry_url' => $assets['cry_latest'] ?? null,
        ]);
    }
}