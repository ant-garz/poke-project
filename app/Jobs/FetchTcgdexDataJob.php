<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Services\ExternalApi\TcgdexClient;
use App\Jobs\ProcessTcgdexCardBatchJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
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

        // mark as processing (start state)
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

        $chunks = array_chunk($ids, 10);

        $jobs = collect($chunks)->map(function ($chunk) use ($pokemon) {
            return new ProcessTcgdexCardBatchJob(
                $pokemon->id,
                $chunk
            );
        });

        Bus::batch($jobs)
            ->name("tcgdex-sync-pokemon-{$pokemon->id}")
            ->then(function (Batch $batch) use ($pokemon) {
                    \Log::info('TCGdex status', [
                        'pokemon_id' => $pokemon->id,
                        'status' => 'completed',
                    ]);
                $pokemon->update([
                    'tcgdex_sync_status' => 'completed',
                    'source_tcgdex_synced_at' => now(),
                ]);
            })
            ->catch(function (Batch $batch, Throwable $e) use ($pokemon) {
                \Log::error('TCGdex status', [
                    'pokemon_id' => $pokemon->id,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                $pokemon->update([
                    'tcgdex_sync_status' => 'failed',
                ]);
            })
            ->dispatch();
    }

    public function failed(Throwable $e): void
    {

        Log::error('Failed FetchTcgdexDataJob', [
            'pokemon_id' => $this->pokemonId,
            'error' => $e->getMessage(),
        ]);
        Pokemon::where('id', $this->pokemonId)
            ->update([
                'tcgdex_sync_status' => 'failed',
            ]);
    }
}