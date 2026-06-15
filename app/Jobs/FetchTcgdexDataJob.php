<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Services\PokemonAssetDownloader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId,
        public string $setCode,
        public string $cardNumber
    ) {}

    public function handle(PokemonAssetDownloader $downloader): void
    {
        $pokemon = Pokemon::find($this->pokemonId);

        if (! $pokemon) {
            return;
        }

        /**
         * 1. Build base TCGDex asset URL
         * Example:
         * https://assets.tcgdex.net/en/swsh/swsh3/136
         */
        $baseAssetUrl = "https://assets.tcgdex.net/en/swsh/{$this->setCode}/{$this->cardNumber}";

        /**
         * 2. Store raw metadata if you're calling SDK/API elsewhere
         */
        $pokemon->update([
            'raw_tcgdex' => [
                'set' => $this->setCode,
                'card_number' => $this->cardNumber,
                'asset_base_url' => $baseAssetUrl,
            ],
            'source_tcgdex_synced_at' => now(),
        ]);

        /**
         * 3. Download ALL card assets
         */
        $assets = $downloader->downloadTcgCardAssets(
            $pokemon,
            $baseAssetUrl
        );

        /**
         * 4. Optional: store “primary” image reference
         * (you still keep full set on disk)
         */
        $pokemon->update([
            'artwork_url' => $assets['high_webp'] ?? null,
        ]);
    }
}