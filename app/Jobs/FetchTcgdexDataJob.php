<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ExternalApi\TcgdexClient;
use Illuminate\Support\Facades\Log;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(TcgdexClient $client): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        /**
         * 1. If already enriched with TCG data, skip
         */
        if (!empty($pokemon->tcg_data)) {
            return;
        }

        /**
         * 2. Try to resolve card by name (fallback strategy)
         */
        $card = null;

        try {
            $card = $client->findCardByName($pokemon->name);
        } catch (\Throwable $e) {
            Log::warning('TCGdex search failed', [
                'pokemon_id' => $pokemon->id,
                'name' => $pokemon->name,
                'error' => $e->getMessage(),
            ]);

            // retry later (transient failure)
            $this->release(10);
            return;
        }

        /**
         * 3. If no match found, retry later (or permanently fail depending on your preference)
         */
        if (!$card) {
            $this->release(30);
            return;
        }

        /**
         * 4. Persist raw card data
         */
        try {
            $pokemon->update([
                'tcg_card_id' => $card->id ?? null,
                'tcg_data' => json_encode($card),
                'description' => $card->description ?? $pokemon->description,
                'sprite_url' => $card->image ?? $pokemon->sprite_url,
                'artwork_url' => $card->image ?? $pokemon->artwork_url,
                'source_tcgdex_synced_at' => now(),
                'source_csv_imported_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed saving TCGdex data', [
                'pokemon_id' => $pokemon->id,
                'error' => $e->getMessage(),
            ]);

            // retry safely
            $this->release(10);
            return;
        }
    }
}