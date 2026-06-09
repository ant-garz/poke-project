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
        $file = storage_path("app/{$this->path}");

        $rows = array_map('str_getcsv', file($file));
        array_shift($rows); // remove header

        foreach ($rows as $row) {
            ParsePokemonCsvRowJob::dispatch($row);
        }
    }
}
