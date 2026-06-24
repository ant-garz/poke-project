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
 * Pokémon import entrypoint.
 *
 * Workflow:
 *
 * ImportPokemonCsvJob
 * ├─ Reads uploaded CSV file
 * ├─ Updates batch metadata (status + row count)
 * ├─ Dispatches ParsePokemonCsvRowJob for each CSV row
 * └─ Schedules FinalizePokemonImportBatchJob
 *
 * ParsePokemonCsvRowJob
 * ├─ Validates and normalizes CSV data
 * ├─ Creates or updates Pokémon records
 * ├─ Creates missing Pokémon types
 * ├─ Assigns primary / secondary type relations
 * ├─ Marks CSV import timestamps
 * └─ Dispatches EnrichPokemonFromExternalApisJob
 *
 * EnrichPokemonFromExternalApisJob
 * ├─ Dispatches FetchPokeApiDataJob
 * └─ Dispatches FetchTcgdexDataJob
 *
 * FetchPokeApiDataJob
 * └─ Synchronizes Pokémon metadata from PokéAPI
 *
 * FetchTcgdexDataJob
 * ├─ Marks TCGdex sync as processing
 * ├─ Retrieves matching card IDs from TCGdex
 * ├─ Chunks card IDs into batches
 * └─ Dispatches ProcessTcgdexCardBatchJob jobs
 *
 * ProcessTcgdexCardBatchJob
 * ├─ Retrieves full card payloads
 * ├─ Upserts card sets
 * ├─ Upserts cards
 * ├─ Rebuilds card attacks
 * └─ Stores pricing and raw TCGdex metadata
 *
 * Notes:
 * - All external API operations are asynchronous.
 * - CSV import completion only tracks row processing.
 * - Pokémon enrichment continues independently after import completion.
 * - TCGdex card synchronization uses queue batches for scalability.
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
