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

    public function syncTcgdex(
        Pokemon $pokemon,
        SyncFromTcgdex $action
    ) {
        $action->execute($pokemon->id);

        return response()->json([
            'message' => 'TCGdex sync completed',
            'pokemon_id' => $pokemon->id
        ]);
    }

    public function syncAll(
        Pokemon $pokemon,
        SyncFromPokeApi $pokeApi,
        SyncFromTcgdex $tcg
    ) {
        $pokeApi->execute($pokemon->id);
        $tcg->execute($pokemon->id);

        return response()->json([
            'message' => 'Full sync completed',
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