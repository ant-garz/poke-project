<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PokemonImportBatch;
use App\Services\PokemonImportService;
use Illuminate\Http\Request;

class PokemonImportController extends Controller
{
    public function __construct(
        private PokemonImportService $service
    ) {}

    /**
     * POST /pokemon/import
     * Upload CSV and kick off ingestion pipeline
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $batch = $this->service->createImportFromFile(
            $request->file('file')
        );

        return response()->json([
            'batch_id' => $batch->id,
            'status' => $batch->status,
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
}