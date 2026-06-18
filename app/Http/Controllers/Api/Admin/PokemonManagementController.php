<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportPokemonCsvJob;
use App\Jobs\EnrichPokemonFromExternalApisJob;
use App\Models\Pokemon;
use Illuminate\Http\Request;

use App\Actions\Pokemon\SyncFromPokeApi;
use App\Actions\Pokemon\SyncFromTcgdex;

class PokemonManagementController extends Controller
{

    public function syncPokeApi(
        Pokemon $pokemon,
        SyncFromPokeApi $action
    ) {
        $action->execute($pokemon->id);

        return response()->json([
            'message' => 'PokeAPI sync completed',
            'pokemon_id' => $pokemon->id
        ]);
    }

    public function syncTcgdex(Pokemon $pokemon)
    {
        $pokemon->update([
            'tcgdex_sync_status' => 'queued',
            'tcg_sync_started_at' => null,
            'source_tcgdex_synced_at' => null,
        ]);

        \App\Jobs\FetchTcgdexDataJob::dispatch($pokemon->id);

        return response()->json([
            'message' => 'TCGdex sync started',
            'pokemon_id' => $pokemon->id,
            'status' => 'queued',
        ]);
    }

    public function syncAll(
        Pokemon $pokemon,
        SyncFromPokeApi $pokeApi
    ) {
        $pokeApi->execute($pokemon->id);

        \App\Jobs\FetchTcgdexDataJob::dispatch($pokemon->id);

        return response()->json([
            'message' => 'Full sync started',
            'pokemon_id' => $pokemon->id,
            'status' => $pokemon->fresh()->tcgdex_sync_status,
        ]);
    }

    public function syncStatus(Pokemon $pokemon)
    {
        return response()->json([
            'pokemon_id' => $pokemon->id,
            'tcgdex_sync_status' => $pokemon->tcgdex_sync_status,
            'tcg_sync_started_at' => $pokemon->tcg_sync_started_at,
        ]);
    }

    /**
     * PATCH /pokemon/{pokemon}
     * Manual admin edit of Pokémon core fields
     */
    public function update(Request $request, Pokemon $pokemon)
    {
        $data = $request->validate([

            // identity
            'name' => ['sometimes', 'string'],
            'slug' => ['sometimes', 'string'],
            'pokedex_number' => ['sometimes', 'integer'],

            // structure
            'is_default' => ['sometimes', 'boolean'],
            'base_pokemon_id' => ['nullable', 'integer'],

            // stats
            'hp' => ['sometimes', 'integer'],
            'attack' => ['sometimes', 'integer'],
            'defense' => ['sometimes', 'integer'],
            'special_attack' => ['sometimes', 'integer'],
            'special_defense' => ['sometimes', 'integer'],
            'speed' => ['sometimes', 'integer'],

            // meta
            'height' => ['nullable', 'integer'],
            'weight' => ['nullable', 'integer'],
            'base_experience' => ['nullable', 'integer'],

            // text
            'description' => ['nullable', 'string'],

            // media (manual override only, optional admin control)
            'sprite_url' => ['nullable', 'string'],
            'pokeapi_artwork_url' => ['nullable', 'string'],
            'tcgdex_artwork_base_url' => ['nullable', 'string'],
            'cry_url' => ['nullable', 'string'],

            // types
            'primary_type_id' => ['nullable', 'integer'],
            'secondary_type_id' => ['nullable', 'integer'],
        ]);

        $pokemon->update($data);

        return response()->json([
            'message' => 'Pokemon updated',
            'pokemon' => $pokemon->fresh()->load([
                'primaryType',
                'secondaryType'
            ])
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