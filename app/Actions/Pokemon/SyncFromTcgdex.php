<?php

namespace App\Actions\Pokemon;

use App\Models\Card;
use App\Models\CardAttack;
use App\Models\CardSet;
use App\Models\Pokemon;
use App\Services\ExternalApi\TcgdexClient;

class SyncFromTcgdex
{
    public function __construct(
        private TcgdexClient $tcg
    ) {}

    public function execute(int $pokemonId): Pokemon
    {
        $pokemon = Pokemon::findOrFail($pokemonId);

        $cardBrief = $this->tcg->findCardByName($pokemon->name);

        if (! $cardBrief) {
            return $pokemon;
        }

        $card = $this->tcg->getCard($cardBrief->id);

        if (! $card) {
            return $pokemon;
        }

        $data = $this->normalize($card);

        /*
        |-------------------------
        | Update Pokémon
        |-------------------------
        */
        $pokemon->update([
            'description' => $data['description'] ?? null,
            'artwork_url' => $data['image'] ?? null,
            'raw_tcgdex' => $data,
            'source_tcgdex_synced_at' => now(),
        ]);

        /*
        |-------------------------
        | Card Set
        |-------------------------
        */
        $set = null;

        if (!empty($data['set']['id'] ?? null)) {
            $set = CardSet::updateOrCreate(
                ['external_id' => (string) $data['set']['id']],
                [
                    'name' => $data['set']['name'] ?? null,
                    'series' => $data['set']['serie'] ?? null,

                    'logo_url' => $data['set']['logo'] ?? null,
                    'symbol_url' => $data['set']['symbol'] ?? null,

                    'card_count_official' => $data['set']['cardCount']['official'] ?? null,
                    'card_count_total' => $data['set']['cardCount']['total'] ?? null,

                    'raw_data' => $data['set'],
                ]
            );
        }

        /*
        |-------------------------
        | Card
        |-------------------------
        */
        $localCard = Card::updateOrCreate(
            [
                'source_tcgdex_id' => (string) $data['id'],
            ],
            [
                'card_set_id' => $set?->id,

                'cardable_id' => $pokemon->id,
                'cardable_type' => Pokemon::class,

                'external_id' => (string) $data['id'],
                'name' => $data['name'] ?? null,
                'number' => $data['localId'] ?? null,

                'hp' => is_numeric($data['hp'] ?? null)
                    ? (int) $data['hp']
                    : null,

                'rarity' => $data['rarity'] ?? null,
                'image_url' => $data['image'] ?? null,

                'supertype' => 'Pokémon',
                'raw_data' => $data,
            ]
        );

        /*
        |-------------------------
        | Attacks (rebuild)
        |-------------------------
        */
        CardAttack::where('card_id', $localCard->id)->delete();

        foreach ($data['attacks'] ?? [] as $attack) {
            CardAttack::create([
                'card_id' => $localCard->id,
                'name' => $attack['name'] ?? null,
                'damage' => isset($attack['damage'])
                    ? (string) $attack['damage']
                    : null,
                'effect' => $attack['effect'] ?? null,
                'cost' => $attack['cost'] ?? [],
            ]);
        }

        return $pokemon->fresh();
    }

    private function normalize(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}