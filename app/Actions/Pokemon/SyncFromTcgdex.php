<?php

namespace App\Actions\Pokemon;

use App\Models\Card;
use App\Models\CardAttack;
use App\Models\CardSet;
use App\Models\Pokemon;

class SyncFromTcgdex
{
    /**
     * Admin entrypoint
     * (keeps same behavior as queue, but fetches internally)
     */
    public function execute(int $pokemonId, array $cardPayloads = null): Pokemon
    {
        $pokemon = Pokemon::findOrFail($pokemonId);

        if ($cardPayloads === null) {
            // Admin fallback path (optional)
            throw new \InvalidArgumentException(
                'Admin sync should pass pre-fetched payloads in new architecture'
            );
        }

        return $this->syncCards($pokemon, $cardPayloads);
    }

    /**
     * Queue-safe bulk processor
     *
     * IMPORTANT:
     * - NO API CALLS HERE
     * - ONLY DB WORK
     */
    public function syncCards(Pokemon $pokemon, array $cards): Pokemon
    {
        if (empty($cards)) {
            return $pokemon;
        }

        $sets = [];
        $firstCard = $cards[0];

        /*
        |-------------------------
        | Collect sets
        |-------------------------
        */
        foreach ($cards as $data) {
            if (!empty($data['set']['id'])) {
                $sets[$data['set']['id']] = $data['set'];
            }
        }

        /*
        |-------------------------
        | Update Pokémon once
        |-------------------------
        */
        if ($pokemon->source_tcgdex_synced_at === null) {
            $pokemon->update([
                'description' => $firstCard['description'] ?? null,
                'tcgdex_artwork_base_url' => $firstCard['image'] ?? null,
                'raw_tcgdex' => $firstCard,
                'source_tcgdex_synced_at' => now(),
            ]);
        }

        /*
        |-------------------------
        | Upsert sets
        |-------------------------
        */
        foreach ($sets as $setId => $setData) {
            CardSet::updateOrCreate(
                ['external_id' => (string) $setId],
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

        $setMap = CardSet::whereIn('external_id', array_keys($sets))
            ->get()
            ->keyBy('external_id');

        /*
        |-------------------------
        | Cards + attacks
        |-------------------------
        */
        foreach ($cards as $data) {

            $setExternalId = $data['set']['id'] ?? null;
            $set = $setExternalId ? $setMap->get((string) $setExternalId) : null;

            $localCard = Card::updateOrCreate(
                ['source_tcgdex_id' => (string) $data['id']],
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

            // rebuild attacks ONLY for this card
            CardAttack::where('card_id', $localCard->id)->delete();

            $attackRows = [];

            foreach ($data['attacks'] ?? [] as $attack) {
                $attackRows[] = [
                    'card_id' => $localCard->id,
                    'name' => $attack['name'] ?? null,
                    'damage' => isset($attack['damage'])
                        ? (string) $attack['damage']
                        : null,
                    'effect' => $attack['effect'] ?? null,
                    'cost' => json_encode($attack['cost'] ?? []),
                ];
            }

            if ($attackRows) {
                CardAttack::insert($attackRows);
            }
        }

        return $pokemon->fresh();
    }
}