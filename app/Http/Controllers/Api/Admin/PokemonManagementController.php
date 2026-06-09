<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportPokemonCsvJob;
use App\Jobs\EnrichPokemonFromExternalApisJob;
use App\Models\Pokemon;
use Illuminate\Http\Request;

class PokemonManagementController extends Controller
{
    /**
     * POST /pokemon/reprocess
     * Re-run full ingestion pipeline manually
     */
    public function reprocess()
    {
        // You might later pass a batch ID or scope
        ImportPokemonCsvJob::dispatch();

        return response()->json([
            'message' => 'Reprocessing job dispatched'
        ]);
    }

    /**
     * POST /pokemon/sync/{pokemon}
     * Force re-fetch external API enrichment
     */
    public function sync(Pokemon $pokemon)
    {
        EnrichPokemonFromExternalApisJob::dispatch($pokemon->id)
            ->delay(now()->addSeconds(10));

        return response()->json([
            'message' => 'Sync queued',
            'pokemon_id' => $pokemon->id
        ]);
    }

    /**
     * PATCH /pokemon/{pokemon}
     * Manual admin edit of Pokémon core fields
     */
    public function update(Request $request, Pokemon $pokemon)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'hp' => ['sometimes', 'integer'],
            'attack' => ['sometimes', 'integer'],
            'defense' => ['sometimes', 'integer'],
            'special_attack' => ['sometimes', 'integer'],
            'special_defense' => ['sometimes', 'integer'],
            'speed' => ['sometimes', 'integer'],
            'description' => ['nullable', 'string'],
            'sprite_url' => ['nullable', 'string'],
            'artwork_url' => ['nullable', 'string'],
        ]);

        $pokemon->update($data);

        return response()->json([
            'message' => 'Pokemon updated',
            'pokemon' => $pokemon->fresh()
        ]);
    }

    /**
     * DELETE /pokemon/{pokemon}
     * Remove Pokémon (rare admin-only action)
     */
    public function destroy(Pokemon $pokemon)
    {
        $pokemon->delete();

        return response()->json([
            'message' => 'Pokemon deleted'
        ]);
    }
}