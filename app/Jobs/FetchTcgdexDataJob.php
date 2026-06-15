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

            $card = $tcg->findCardByName($pokemon->name);

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

                // safest way to persist SDK object
                'raw_tcgdex' => json_decode(
                    json_encode($card),
                    true
                ),

                'source_tcgdex_synced_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Sync Card Set
            |--------------------------------------------------------------------------
            */

            $set = null;

            if (isset($card->set)) {

                $set = CardSet::updateOrCreate(
                    [
                        'external_id' => $card->set->id,
                    ],
                    [
                        'name' => $card->set->name ?? null,
                        'series' => $card->set->serie ?? null,

                        'logo_url' => $card->set->logo ?? null,
                        'symbol_url' => $card->set->symbol ?? null,

                        'card_count_official'
                            => $card->set->cardCount->official ?? null,

                        'card_count_total'
                            => $card->set->cardCount->total ?? null,

                        'raw_data' => json_decode(
                            json_encode($card->set),
                            true
                        ),
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Sync Card
            |--------------------------------------------------------------------------
            */

            $localCard = Card::updateOrCreate(
                [
                    'source_tcgdex_id' => $card->id,
                ],
                [
                    'card_set_id' => $set?->id,

                    'cardable_id' => $pokemon->id,
                    'cardable_type' => Pokemon::class,

                    'external_id' => $card->id,
                    'source_tcgdex_id' => $card->id,

                    'name' => $card->name ?? null,
                    'number' => $card->localId ?? null,

                    'hp' => $card->hp ?? null,
                    'rarity' => $card->rarity ?? null,

                    'image_url' => $card->image ?? null,

                    'supertype' => 'Pokémon',

                    'raw_data' => json_decode(
                        json_encode($card),
                        true
                    ),
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

            DownloadPokemonAssetsJob::dispatch(
                $pokemon->id
            )->onQueue('assets');

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
}