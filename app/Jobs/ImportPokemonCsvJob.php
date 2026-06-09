<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\PokemonImportBatch;
use App\Jobs\ParsePokemonCsvRowJob;
use App\Enums\PokemonImportBatchStatus;
use App\Jobs\FinalizePokemonImportBatchJob;

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

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = PokemonImportBatch::findOrFail($this->batchId);

        if (!Storage::exists($batch->file_path)) {
            throw new \Exception("CSV file not found: {$batch->file_path}");
        }

        $contents = Storage::get($batch->file_path);

        $lines = array_filter(explode("\n", trim($contents)));

        $headers = str_getcsv(array_shift($lines));

        $batch->update([
            'status' => PokemonImportBatchStatus::Processing,
            'total_rows' => count($lines),
        ]);

        foreach ($lines as $index => $line) {
            $row = array_combine($headers, str_getcsv($line));

            ParsePokemonCsvRowJob::dispatch($batch->id, $row);
        }

        FinalizePokemonImportBatchJob::dispatch($batch->id)
            ->delay(now()->addSeconds(5));
    }
}
