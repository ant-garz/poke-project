<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Services\ExternalApi\TcgdexClient;
use App\Jobs\ProcessTcgdexCardBatchJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(TcgdexClient $tcg): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $rawCards = $tcg->findCardsByName($pokemon->name);

        $cardBriefs = collect($rawCards)
            ->filter(fn ($card) =>
                isset($card->name)
                && $this->isValidPokemonCardMatch($pokemon->name, $card->name)
            )
            ->values();

        if ($cardBriefs->isEmpty()) {
            \Log::info('No TCG cards matched', [
                'pokemon_id' => $pokemon->id,
                'pokemon' => $pokemon->name,
            ]);
            return;
        }

        $ids = $cardBriefs
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        \Log::info('Dispatching TCG batches', [
            'pokemon_id' => $pokemon->id,
            'pokemon' => $pokemon->name,
            'total_cards' => count($ids),
        ]);

        foreach (array_chunk($ids, 25) as $chunk) {
            ProcessTcgdexCardBatchJob::dispatch(
                $pokemon->id,
                $chunk
            );
        }
    }

    private function isValidPokemonCardMatch(string $pokemonName, string $cardName): bool
    {
        $pokemon = strtolower($pokemonName);
        $card = strtolower($cardName);

        if ($card === $pokemon) return true;

        $cleanCard = preg_replace(
            '/^(dark|shining|team rocket\'s|rocket\'s|base|shadow|delta)\s+/i',
            '',
            $card
        );

        if ($cleanCard === $pokemon) return true;

        if (str_starts_with($card, $pokemon . ' ')) return true;

        $normalizedPokemon = preg_replace('/[^a-z0-9]/', '', $pokemon);
        $normalizedCard = preg_replace('/[^a-z0-9]/', '', $card);

        return $normalizedPokemon === $normalizedCard;
    }

    public function failed(Throwable $e): void
    {
        \Log::error('FetchTcgdexDataJob failed', [
            'pokemon_id' => $this->pokemonId,
            'message' => $e->getMessage(),
        ]);
    }
}