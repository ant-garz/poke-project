<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PokemonImportBatch;
use App\Services\PokemonImportService;
use Illuminate\Http\Request;

use App\Enums\PokemonImportBatchStatus;
use App\Jobs\ImportPokemonCsvJob;

class PokemonImportController extends Controller
{
    public function __construct(
        private PokemonImportService $service
    ) {}

    public function index()
    {
        return \App\Models\PokemonImportBatch::query()
            ->latest()
            ->paginate(20);
    }

    /**
     * POST /pokemon/import
     * Upload CSV and kick off ingestion pipeline
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        // 1. Store file properly in storage/app/imports/pokemon
        $path = $request->file('file')->store('imports/pokemon');

        // 2. Create batch record
        $batch = PokemonImportBatch::create([
            'file_path' => $path,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'status' => PokemonImportBatchStatus::Uploaded,
            'progress' => 0,
        ]);

        // 3. Kick off job
        ImportPokemonCsvJob::dispatch($batch->id);

        return response()->json([
            'batch_id' => $batch->id,
            'status' => 'started',
        ]);
    }

    /**
     * GET /pokemon/import/{batch}
     * View progress of an import batch
     */
    public function show(PokemonImportBatch $batch)
    {
        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'failed_rows' => $batch->failed_rows,
            'meta' => $batch->meta,
            'created_at' => $batch->created_at,
        ]);
    }

    public function detail(\App\Models\PokemonImportBatch $batch)
    {
        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,

            'original_filename' => $batch->original_filename,
            'file_path' => $batch->file_path,

            'total_rows' => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'failed_rows' => $batch->failed_rows,

            'progress' => $batch->progress(),

            'meta' => $batch->meta,
            'created_at' => $batch->created_at,
            'updated_at' => $batch->updated_at,
        ]);
    }
}