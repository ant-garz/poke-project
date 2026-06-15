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

        /**
         * STEP 1: Mark SUCCESSFUL enrichments
         */
        Pokemon::where('batch_id', $batch->id)
            ->whereNotNull('source_pokeapi_synced_at')
            ->whereNotNull('source_tcgdex_synced_at')
            ->update([
                'is_enriched' => true,
            ]);

        /**
         * STEP 2: Mark FAILED / INCOMPLETE enrichments
         */
        Pokemon::where('batch_id', $batch->id)
            ->where(function ($q) {
                $q->whereNull('source_pokeapi_synced_at')
                  ->orWhereNull('source_tcgdex_synced_at');
            })
            ->update([
                'is_enriched' => false,
            ]);

        /**
         * STEP 3: Recalculate batch stats from truth
         */
        $enrichedCount = Pokemon::where('batch_id', $batch->id)
            ->where('is_enriched', true)
            ->count();

        $failedCount = Pokemon::where('batch_id', $batch->id)
            ->where('is_enriched', false)
            ->count();

        /**
         * STEP 4: Update batch
         */
        $batch->update([
            'processed_rows' => $enrichedCount + $failedCount,
            'failed_rows' => $failedCount,
        ]);

        /**
         * STEP 5: finalize batch
         */
        if (($enrichedCount + $failedCount) >= $batch->total_rows) {
            $batch->update([
                'status' => PokemonImportBatchStatus::Completed,
            ]);
        }
    }
}