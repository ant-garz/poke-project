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

    public int $timeout = 600;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(TcgdexClient $tcg): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $pokemon->update([
            'tcgdex_sync_status' => 'processing',
            'tcg_sync_started_at' => now(),
        ]);

        $cardBriefs = $tcg->findCardsByName($pokemon->name);

        if (empty($cardBriefs)) {
            $pokemon->update([
                'tcgdex_sync_status' => 'completed',
                'source_tcgdex_synced_at' => now(),
            ]);
            return;
        }

        $ids = collect($cardBriefs)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        foreach (array_chunk($ids, 10) as $chunk) {
            ProcessTcgdexCardBatchJob::dispatch(
                $pokemon->id,
                $chunk
            );
        }

        \Log::info('TCGdex job dispatched', [
            'pokemon_id' => $pokemon->id,
            'total_cards' => count($ids),
            'chunks' => count(array_chunk($ids, 10)),
        ]);

        // IMPORTANT:
        // still processing until batch jobs finish
        $pokemon->update([
            'tcgdex_sync_status' => 'processing',
        ]);
    }

    public function failed(Throwable $e): void
    {
        Pokemon::where('id', $this->pokemonId)
            ->update([
                'tcgdex_sync_status' => 'failed',
            ]);

        \Log::error('TCGdex job failed', [
            'pokemon_id' => $this->pokemonId,
            'error' => $e->getMessage(),
        ]);
    }
}