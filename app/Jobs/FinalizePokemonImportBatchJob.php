<?php

namespace App\Jobs;

use App\Models\Pokemon;
use App\Models\PokemonImportBatch;
use App\Enums\PokemonImportBatchStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FinalizePokemonImportBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = PokemonImportBatch::findOrFail($this->batchId);

        /*
        |--------------------------------------------------------------------------
        | STEP 1: Validate / normalize Pokémon enrichment state
        |--------------------------------------------------------------------------
        |
        | Even though Pokémon are NOT batch-scoped, we still ensure their
        | enrichment flags are consistent with actual API results.
        |
        | This acts as a safety reconciliation pass.
        |
        */

        Pokemon::query()
            ->whereNotNull('source_csv_imported_at')
            ->chunkById(500, function ($pokemonBatch) {
                foreach ($pokemonBatch as $pokemon) {

                    $isEnriched =
                        !is_null($pokemon->source_pokeapi_synced_at) &&
                        !is_null($pokemon->source_tcgdex_synced_at);

                    if ($pokemon->is_enriched !== $isEnriched) {
                        $pokemon->update([
                            'is_enriched' => $isEnriched,
                        ]);
                    }
                }
            });

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Batch progress calculation (IMPORT TRACKING ONLY)
        |--------------------------------------------------------------------------
        |
        | DO NOT infer Pokémon membership from batch_id.
        | rely entirely on batch counters (authoritative source).
        |
        */

        $processed = ($batch->processed_rows ?? 0) + ($batch->failed_rows ?? 0);
        $total = $batch->total_rows ?? 0;

        /*
        |--------------------------------------------------------------------------
        | STEP 3: Update batch stats
        |--------------------------------------------------------------------------
        */

        $batch->update([
            'processed_rows' => $processed,
            'failed_rows' => $batch->failed_rows ?? 0,
        ]);

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Finalize batch if complete
        |--------------------------------------------------------------------------
        */

        if ($total > 0 && $processed >= $total) {
            $batch->update([
                'status' => PokemonImportBatchStatus::Completed,
            ]);
        }
    }
}