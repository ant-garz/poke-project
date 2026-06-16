<?php

namespace App\Jobs;

use App\Models\Card;
use App\Models\CardAttack;
use App\Models\CardSet;
use App\Models\Pokemon;
use App\Services\ExternalApi\TcgdexClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(TcgdexClient $tcg): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        try {

            /*
            |--------------------------------------------------------------------------
            | Find Card
            |--------------------------------------------------------------------------
            */

            $cardBrief = $tcg->findCardByName($pokemon->name);

            if (! $cardBrief) {
                return;
            }

            $card = $tcg->getCard($cardBrief->id);

            if (! $card) {
                return;
            }

            if (! $card) {

                Log::warning('TCG card not found', [
                    'pokemon_id' => $pokemon->id,
                    'pokemon_name' => $pokemon->name,
                ]);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Pokemon
            |--------------------------------------------------------------------------
            */

            $pokemon->update([
                'description' => $card->description ?? null,
                'artwork_url' => $card->image ?? null,
                'raw_tcgdex' =>  $this->normalize_to_array($card),

                'source_tcgdex_synced_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Sync Card Set
            |--------------------------------------------------------------------------
            */

            $set = null;

            if (isset($card->set) && is_object($card->set)) {

                $setData = json_decode(json_encode($card->set), true);

                if (!empty($setData['id'] ?? null)) {

                    $set = CardSet::updateOrCreate(
                        [
                            'external_id' => (string) $setData['id'],
                        ],
                        [
                            'name' => $setData['name'] ?? null,
                            'series' => $setData['serie'] ?? null,

                            'logo_url' => $setData['logo'] ?? null,
                            'symbol_url' => $setData['symbol'] ?? null,

                            'card_count_official' => $setData['cardCount']['official'] ?? null,
                            'card_count_total' => $setData['cardCount']['total'] ?? null,

                            'raw_data' => $setData,
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Sync Card
            |--------------------------------------------------------------------------
            */
            $localCard = Card::updateOrCreate(
                [
                    'source_tcgdex_id' => (string) $card->id,
                ],
                [
                    // SAFE nullable FK
                    'card_set_id' => $set?->id,

                    'cardable_id' => $pokemon->id,
                    'cardable_type' => Pokemon::class,

                    'external_id' => (string) $card->id,
                    'source_tcgdex_id' => (string) $card->id,

                    'name' => $card->name ?? null,
                    'number' => $card->localId ?? null,

                    'hp' => is_numeric($card->hp ?? null) ? (int) $card->hp : null,
                    'rarity' => $card->rarity ?? null,

                    'image_url' => $card->image ?? null,

                    'supertype' => 'Pokémon',

                    // ALWAYS safe JSON
                    'raw_data' => $this->normalize_to_array($card),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Sync Attacks
            |--------------------------------------------------------------------------
            */

            CardAttack::where(
                'card_id',
                $localCard->id
            )->delete();

            foreach ($card->attacks ?? [] as $attack) {

                CardAttack::create([
                    'card_id' => $localCard->id,

                    'name' => $attack->name ?? null,

                    'damage' => isset($attack->damage)
                        ? (string) $attack->damage
                        : null,

                    'effect' => $attack->effect ?? null,

                    'cost' => $attack->cost ?? [],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Queue Asset Download
            |--------------------------------------------------------------------------
            */

            /*
            DownloadPokemonAssetsJob::dispatch(
                $pokemon->id
            );
            */

            Log::info('TCG sync completed', [
                'pokemon_id' => $pokemon->id,
                'card_id' => $card->id,
            ]);

        } catch (\Throwable $e) {

            Log::error('TCG sync failed', [
                'pokemon_id' => $pokemon->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
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