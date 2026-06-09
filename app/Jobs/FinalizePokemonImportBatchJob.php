<?php

namespace App\Jobs;

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

        $totalProcessed =
            ($batch->processed_rows ?? 0) +
            ($batch->failed_rows ?? 0);

        if ($batch->total_rows === null) {
            return;
        }

        if ($totalProcessed >= $batch->total_rows) {
            $batch->update([
                'status' => PokemonImportBatchStatus::Completed,
            ]);
        }
    }
}