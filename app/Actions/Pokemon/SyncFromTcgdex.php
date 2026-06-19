<?php

namespace App\Actions\Pokemon;

use App\Models\Card;
use App\Models\CardAttack;
use App\Models\CardSet;
use App\Models\Pokemon;

class SyncFromTcgdex
{
    public function __construct(
        private \App\Services\ExternalApi\TcgdexClient $tcg
    ) {}

    /**
     * Admin entrypoint (FULL SYNC)
     */
    public function execute(int $pokemonId): Pokemon
    {
        $pokemon = Pokemon::findOrFail($pokemonId);

        // admin now uses job instead
        throw new \RuntimeException(
            'Manual execute no longer supported. Use queue job.'
        );
    }

    /**
     * Core sync (queue-safe)
     */
    public function syncCards(int $pokemonId, array $cardIds): Pokemon
    {
        $pokemon = Pokemon::findOrFail($pokemonId);

        $cards = [];
        $sets = [];

        foreach ($cardIds as $cardId) {

            $card = $this->tcg->getCard($cardId);

            if (! $card) {
                continue;
            }

            $data = $this->normalize($card);

            $cards[] = $data;

            if (!empty($data['set']['id'] ?? null)) {
                $sets[$data['set']['id']] = $data['set'];
            }
        }

        if (empty($cards)) {
            return $pokemon;
        }

        $firstCard = $cards[0];

        if ($pokemon->source_tcgdex_synced_at === null) {
            $pokemon->update([
                'description' => $firstCard['description'] ?? null,
                'tcgdex_artwork_base_url' => $firstCard['image'] ?? null,
                'raw_tcgdex' => $firstCard,
            ]);
        }

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

        foreach ($cards as $data) {

            $set = $setMap->get((string) ($data['set']['id'] ?? null));

            $localCard = Card::updateOrCreate(
                ['source_tcgdex_id' => (string) $data['id']],
                [
                    'card_set_id' => $set?->id,
                    'cardable_id' => $pokemon->id,
                    'cardable_type' => Pokemon::class,

                    'external_id' => (string) $data['id'],
                    'name' => $data['name'] ?? null,
                    'number' => $data['localId'] ?? null,
                    'hp' => is_numeric($data['hp'] ?? null) ? (int) $data['hp'] : null,
                    'rarity' => $data['rarity'] ?? null,
                    'image_url' => $data['image'] ?? null,
                    'supertype' => 'Pokémon',
                    'raw_data' => $data,
                ]
            );

            CardAttack::where('card_id', $localCard->id)->delete();

            $attackRows = [];

            foreach ($data['attacks'] ?? [] as $attack) {
                $attackRows[] = [
                    'card_id' => $localCard->id,
                    'name' => $attack['name'] ?? null,
                    'damage' => $attack['damage'] ?? null,
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

    private function normalize(mixed $card): array
    {
        return json_decode(json_encode($card), true) ?? [];
    }

    private function filterValidCards(string $pokemonName, array $cards): array
    {
        return array_values(array_filter(
            $cards,
            fn ($card) =>
                isset($card['name']) &&
                $this->isValidPokemonCardMatch($pokemonName, $card['name'])
        ));
    }

    private function isValidPokemonCardMatch(string $pokemonName, string $cardName): bool
    {
        $pokemon = strtolower($pokemonName);
        $card = strtolower($cardName);

        if ($card === $pokemon) return true;

        $cleanCard = preg_replace(
            '/^(dark|shining|team rocket\'s|rocket\'s|base|shadow|delta|ancient)\s+/i',
            '',
            $card
        );

        if ($cleanCard === $pokemon) return true;

        if (str_starts_with($card, $pokemon . ' ')) return true;

        $normalizedPokemon = preg_replace('/[^a-z0-9]/', '', $pokemon);
        $normalizedCard = preg_replace('/[^a-z0-9]/', '', $card);

        return $normalizedPokemon === $normalizedCard;
    }
}