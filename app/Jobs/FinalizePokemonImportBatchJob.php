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
        | Batch progress calculation (IMPORT TRACKING ONLY)
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
        | Update batch stats
        |--------------------------------------------------------------------------
        */

        $batch->update([
            'processed_rows' => $processed,
            'failed_rows' => $batch->failed_rows ?? 0,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Finalize batch if complete
        |--------------------------------------------------------------------------
        */

        if ($total > 0 && $processed >= $total) {
            $batch->update([
                'status' => PokemonImportBatchStatus::Completed,
            ]);
        }
    }
}