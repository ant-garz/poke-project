<?php

namespace App\Services;

use App\Models\PokemonImportBatch;
use App\Jobs\ImportPokemonCsvJob;

class PokemonImportService
{
    public function createImportFromFile($file): PokemonImportBatch
    {
        $path = $file->store('pokemon-imports');

        $batch = PokemonImportBatch::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'uploaded',
        ]);

        // 🚀 Kick off your pipeline
        ImportPokemonCsvJob::dispatch($batch->id);

        return $batch;
    }

    public function getBatchStatus(int $batchId): PokemonImportBatch
    {
        return PokemonImportBatch::query()
            ->with([])
            ->findOrFail($batchId);
    }
}