<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * This kicks off main workflow
 * ImportPokemonCsvJob
 *  → reads file + dispatches row jobs
 *  ParsePokemonCsvRowJob
 *  → converts raw CSV row → Pokémon DB write/update
 *  EnrichPokemonFromExternalApisJob
 *  → orchestrator job (calls API jobs)
 *  FetchTcgdexDataJob
 *  → returns structured TCGDex data
 *  FetchPokeApiDataJob
 *  → returns structured PokeAPI data
 */

class ImportPokemonCsvJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $path) {}

    public function handle(): void
    {
        $batch = PokemonImportBatch::findOrFail($this->batchId);

        // 1. Get file from storage
        if (!Storage::exists($batch->file_path)) {
            throw new \Exception("CSV file not found: {$batch->file_path}");
        }

        $contents = Storage::get($batch->file_path);

        // 2. Convert to lines
        $lines = array_filter(explode("\n", trim($contents)));

        $headers = str_getcsv(array_shift($lines));

        foreach ($lines as $index => $line) {
            $row = array_combine($headers, str_getcsv($line));

            // dispatch row job (your existing pipeline)
            ParsePokemonCsvRowJob::dispatch($batch->id, $row);
        }

        $batch->update([
            'status' => 'processing',
            'total_rows' => count($lines),
        ]);
    }
}
